<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockControlRepository
{
    use LogsActivity;

    public function __construct(private MedicineInventoryService $inventory)
    {
    }

    public function preview(array $data, User $actor): array
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($data, $actor): array {
            $state = $this->state($data, false);
            $state['token'] = Crypt::encryptString(json_encode(['data' => $data, 'actor' => $actor->id,
                'expires' => now()->addMinutes(15)->timestamp, 'nonce' => Str::uuid()->toString(), 'fingerprint' => $state['fingerprint']], JSON_THROW_ON_ERROR));

            return $state;
        });
    }

    public function confirm(string $token, User $actor): Medicine
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($token, $actor): Medicine {
            try {
                $approval = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
                if ($approval['actor'] !== $actor->id || $approval['expires'] < now()->timestamp) {
                    throw new \RuntimeException('Expired preview');
                }
            } catch (\Throwable $error) {
                throw ValidationException::withMessages(['preview_token' => 'This preview is invalid or expired. Preview again.']);
            }
            $state = $this->state($approval['data'], true);
            if (! hash_equals($state['fingerprint'], $approval['fingerprint'])
                || ActivityLog::where('properties->stock_preview_nonce', $approval['nonce'])->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['preview_token' => 'Stock changed or this preview was already confirmed. Preview again.']);
            }
            $data = $approval['data'];
            $audit = self::logActivity($data['mode'] === 'count' ? 'stock_count' : 'stock_disposal', 'Batch '.$data['mode'].' for medicine #'.$state['medicine']->id, [
                'subject_type' => Medicine::class, 'subject_id' => $state['medicine']->id,
                'properties' => ['mode' => $data['mode'], 'reason' => $data['reason'], 'changes' => $state['changes'], 'stock_preview_nonce' => $approval['nonce']],
            ]);
            if (! $audit || ! $audit->exists) {
                throw new \RuntimeException('The audit could not be recorded.');
            }
            foreach ($state['changes'] as $change) {
                $this->inventory->recordBatchCount($change['batch_id'], $change['after'], $actor->id,
                    $data['mode'].': '.$data['reason'], $audit, $data['mode'] !== 'count');
            }

            return $state['medicine']->fresh();
        }, 5);
    }

    private function authorize(User $actor): void
    {
        abort_unless($actor->hasAnyRole(['clinic_admin', 'staff']) && $actor->can('manage_medicines') && $actor->status && ! $actor->trashed(), 403);
    }

    private function state(array $data, bool $lock): array
    {
        $query = Medicine::whereKey($data['medicine_id']);
        $medicine = ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
        $query = MedicineBatch::where('medicine_id', $medicine->id)->orderBy('id');
        $batches = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('id');
        $entries = array_filter($data['entries'], fn ($value) => $value !== null && $value !== '');
        if (! $entries) {
            throw ValidationException::withMessages(['entries' => 'Enter at least one batch quantity.']);
        }
        ksort($entries);
        $changes = [];
        foreach ($entries as $id => $value) {
            $batch = $batches->get($id);
            $quantity = (int) $value;
            if (! $batch || ! ctype_digit((string) $id) || ($data['mode'] !== 'count' && ($quantity < 1 || $quantity > $batch->quantity))
                || ($data['mode'] === 'expired' && (! $batch->expiration_date || $batch->expiration_date->toDateString() >= today()->toDateString()))) {
                throw ValidationException::withMessages(['entries' => 'Choose batches of this medicine, valid disposal quantities, and past-expiry stock for expired disposal.']);
            }
            $after = $data['mode'] === 'count' ? $quantity : $batch->quantity - $quantity;
            $changes[] = ['batch_id' => $batch->id, 'batch' => $batch->batch_number, 'dosage' => $batch->dosage,
                'expiry' => $batch->expiration_date?->toDateString(), 'before' => $batch->quantity, 'after' => $after, 'difference' => $after - $batch->quantity];
        }
        $latestLedger = MedicineTransaction::whereIn('batch_id', $batches->keys())->orderByDesc('id');
        $lastId = ($lock ? $latestLedger->lockForUpdate() : $latestLedger)->value('id');

        return ['medicine' => $medicine, 'changes' => $changes, 'mode' => $data['mode'], 'reason' => $data['reason'],
            'fingerprint' => hash('sha256', json_encode([$medicine->getAttributes(), $batches->map->getAttributes()->all(), $lastId, today()->toDateString()], JSON_THROW_ON_ERROR))];
    }
}
