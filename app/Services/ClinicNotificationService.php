<?php

namespace App\Services;

use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\PatientQueue;
use App\Models\User;
use App\Support\InventoryAlerts;
use Illuminate\Support\Facades\DB;

/** Persist conditions after source commits; reading the bell or center never generates notifications. */
class ClinicNotificationService
{
    public function refresh(?string $module = null): void
    {
        // Old working installations remain readable until their next migration.
        if (! Notification::supportsClinicAlerts()) {
            return;
        }
        $modules = $module === null ? ['inventory', 'queue', 'consultations'] : [$module];
        if (! array_intersect($modules, ['inventory', 'queue', 'consultations'])) {
            return;
        }
        User::where('status', true)->role(['clinic_admin', 'staff', 'doctor'])->select('users.id')->chunkById(100, function ($users) use ($modules) {
            foreach ($users as $recipient) {
                DB::transaction(function () use ($recipient, $modules) {
                    // The first read is a current, locked read: competing refreshes cannot duplicate or revive a stale alert.
                    $user = User::whereKey($recipient->id)->lockForUpdate()->first();
                    if (! $user || ! $user->status) {
                        return;
                    }
                    foreach ($modules as $module) {
                        $conditions = $this->conditions($module, $user);
                        $active = Notification::where('user_id', $user->id)->where('module', $module)->whereNull('resolved_at')->lockForUpdate()->get();
                        foreach ($active as $alert) {
                            if (! isset($conditions[$alert->event_key])) {
                                $alert->update(['event_key' => null, 'resolved_at' => now()]);
                            }
                        }
                        foreach ($conditions as $key => $data) {
                            $data['title'] = mb_substr($data['title'], 0, 255);
                            $existing = $active->firstWhere('event_key', $key);
                            if (! $existing) {
                                Notification::create($data + ['user_id' => $user->id, 'module' => $module, 'event_key' => $key]);
                            } else {
                                $existing->fill($data);
                                if ($existing->isDirty()) {
                                    $existing->save();
                                }
                            }
                        }
                    }
                }, 5);
            }
        });
    }

    private function conditions(string $module, User $user): array
    {
        if (! canUseModule($module, $user)) {
            return [];
        }
        $conditions = [];
        if ($module === 'inventory') {
            foreach (Medicine::with('batches')->get() as $medicine) {
                // Availability is evaluated as of today, including when the nightly aggregate sync has not run yet.
                if ($medicine->batches->isNotEmpty()) {
                    $medicine->available_quantity = $medicine->batches->filter(fn ($b) => $b->expiration_date === null || $b->expiration_date->toDateString() >= today()->toDateString())->sum('quantity');
                }
                if ($medicine->isLowStock()) {
                    $conditions['low:'.$medicine->id] = ['type' => 'stock_low', 'title' => 'Low stock: '.$medicine->name,
                        'alert_details' => 'Usable units: '.$medicine->available_quantity, 'target_id' => $medicine->id];
                }
                foreach ($medicine->batches as $batch) {
                    if ($batch->quantity <= 0 || ! $batch->expiration_date || $batch->expiration_date->toDateString() > today()->addDays(InventoryAlerts::WARNING_DAYS)->toDateString()) {
                        continue;
                    }
                    $expired = $batch->expiration_date->toDateString() < today()->toDateString();
                    $conditions[($expired ? 'expired:' : 'expiring:').$batch->id] = ['type' => $expired ? 'stock_expired' : 'stock_expiring',
                        'title' => ($expired ? 'Expired stock: ' : 'Expiring stock: ').$medicine->name,
                        'alert_details' => 'Batch #'.$batch->id.' · expiry '.$batch->expiration_date->toDateString(), 'target_id' => $medicine->id];
                }
            }
        } elseif ($user->hasRole('doctor') && $module === 'queue') {
            foreach (PatientQueue::openToday()->where('status', PatientQueue::STATUS_WAITING)->pluck('id') as $id) {
                $conditions['queue:'.$id] = ['type' => 'queue_new', 'title' => 'New waiting queue entry', 'alert_details' => 'Open the queue to review entry #'.$id.'.', 'target_id' => $id];
            }
        } elseif ($user->hasRole('doctor') && $module === 'consultations') {
            foreach (DocumentIssuance::incompleteConsultations()->pluck('id') as $id) {
                $conditions['assessment:'.$id] = ['type' => 'consultation_incomplete', 'title' => 'Consultation needs assessment', 'alert_details' => 'Assessment or Plan is missing on form #'.$id.'.', 'target_id' => $id];
            }
        }

        return $conditions;
    }
}
