<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockControlRepository
{
    use LogsActivity;

    /** Cache key prefix of a preview a confirmation has used up; it outlives the preview's 15 minutes. */
    private const CLAIM_PREFIX = 'stock-preview-nonce:';

    private const PREVIEW_USED = 'Stock changed or this preview was already confirmed. Preview again.';

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
            if (! hash_equals($state['fingerprint'], $approval['fingerprint']) || $this->wasConfirmed($approval['nonce'])) {
                throw ValidationException::withMessages(['preview_token' => self::PREVIEW_USED]);
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

            // Claimed last, so a confirmation that fails midway does not use the preview up. Two confirmations of one preview
            // are serialized by the medicine lock above, but the second one's transaction may have started before the first
            // committed, so it cannot be sure to see the first one's audit row; the atomic claim settles it, and throwing here
            // rolls this whole confirmation back. No lock on the audit table is needed (or wanted: it stalled every audit write).
            if (! Cache::add(self::CLAIM_PREFIX.$approval['nonce'], true, now()->addMinutes(20))) {
                throw ValidationException::withMessages(['preview_token' => self::PREVIEW_USED]);
            }

            return $state['medicine']->fresh();
        }, 5);
    }

    /** Whether a confirmation already wrote this preview's audit row (the stock actions are found through the indexed action column). */
    private function wasConfirmed(string $nonce): bool
    {
        return ActivityLog::whereIn('action', ['stock_count', 'stock_disposal'])
            ->where('properties->stock_preview_nonce', $nonce)->exists();
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
