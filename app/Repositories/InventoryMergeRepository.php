<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\User;
use App\Services\MedicineInventoryService;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryMergeRepository
{
    use LogsActivity;

    public const MODELS = ['medicine' => Medicine::class, 'category' => Category::class, 'generic' => Generic::class];

    private const MEDICINE_REFERENCES = [
        'medicine_batches' => 'medicine_id', 'consultation_medicines' => 'medicine_id',
        'prescriptions_medicines' => 'medicine', 'sale_medicines' => 'medicine_id',
        'purchased_medicines' => 'medicine_id', 'used_medicines' => 'medicine_id',
    ];

    private const LABELS = ['medicine_batches' => 'Stock batches', 'medicine_transactions' => 'Ledger entries (batch links retained)',
        'consultation_medicines' => 'Consultation lines', 'prescriptions_medicines' => 'Prescription lines',
        'sale_medicines' => 'Dispense lines', 'purchased_medicines' => 'Stock-in lines', 'used_medicines' => 'Legacy usage lines',
        'medicines' => 'Medicine labels', 'generics' => 'Linked generic labels'];

    public function __construct(private MedicineInventoryService $inventory)
    {
    }

    public function preview(string $kind, int $sourceId, int $targetId, User $actor): array
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($kind, $sourceId, $targetId, $actor): array {
            $state = $this->state($kind, $sourceId, $targetId, false);
            $counts = [];
            foreach ($state['rows'] as $table => $rows) {
                $counts[] = ['label' => self::LABELS[$table], 'count' => count($this->sourceRows($kind, $table, $rows, $state))];
            }
            $token = $state['blockers'] ? null : Crypt::encryptString(json_encode([
                'kind' => $kind, 'source' => $sourceId, 'target' => $targetId, 'actor' => $actor->id,
                'expires' => now()->addMinutes(15)->timestamp, 'fingerprint' => $state['fingerprint'],
            ], JSON_THROW_ON_ERROR));

            return ['source' => $state['source'], 'target' => $state['target'], 'counts' => $counts,
                'blockers' => $state['blockers'], 'batches' => $state['batch_plan'], 'stock' => $state['stock'], 'token' => $token];
        });
    }

    public function merge(string $kind, int $sourceId, int $targetId, string $token, User $actor): Model
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($kind, $sourceId, $targetId, $token, $actor): Model {
            $state = $this->state($kind, $sourceId, $targetId, true);
            try {
                $approval = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
                $valid = $approval['kind'] === $kind && $approval['source'] === $sourceId && $approval['target'] === $targetId
                    && $approval['actor'] === $actor->id && $approval['expires'] >= now()->timestamp
                    && hash_equals($state['fingerprint'], $approval['fingerprint']) && ! $state['blockers'];
            } catch (\Throwable $error) {
                $valid = false;
            }
            if (! $valid) {
                throw ValidationException::withMessages(['preview_token' => 'The preview expired or these records changed. Preview the merge again.']);
            }

            $source = $state['source_model'];
            $target = $state['target_model'];
            $renames = [];
            if ($kind === 'medicine') {
                $renames = $this->inventory->mergeMedicineStock($source, $target, $state['batch_plan']);
                $strength = trim((string) $source->dosage) ?: 'N/A';
                foreach (self::MEDICINE_REFERENCES as $table => $column) {
                    if ($table === 'medicine_batches') {
                        continue;
                    }
                    $query = DB::table($table)->where($column, $sourceId);
                    if (Schema::hasColumn($table, 'dosage')) {
                        (clone $query)->where(fn (Builder $row) => $row->whereNull('dosage')->orWhereRaw('TRIM(dosage) = ?', ['']))->update(['dosage' => $strength]);
                    }
                    $query->update([$column => $targetId]);
                }
            } else {
                $changes = [$kind.'_id' => $targetId];
                $changes[$kind === 'category' ? 'category' : 'generic_name'] = $target->name;
                if ($kind === 'category') {
                    $changes['category_name'] = $target->name;
                }
                $this->medicineLabels($kind, [$sourceId], [$source->name])->update($changes);
                if ($kind === 'category' && Schema::hasColumn('generics', 'category_id')) {
                    DB::table('generics')->where('category_id', $sourceId)->update(['category_id' => $targetId]);
                }
            }
            $source->forceFill(['merged_into_id' => $targetId, 'merged_at' => now(), 'merged_by' => $actor->id])->save();
            $moved = [];
            foreach ($state['rows'] as $table => $rows) {
                $moved[$table] = array_column($this->sourceRows($kind, $table, $rows, $state), 'id');
            }
            // Audit failure aborts the entire transaction; clinical row values are not copied into the trail.
            self::logActivity('inventory_merged', ucfirst($kind).' #'.$sourceId.' merged into #'.$targetId, [
                'subject_type' => self::MODELS[$kind], 'subject_id' => $targetId,
                'properties' => ['kind' => $kind, 'source' => $state['source'], 'target' => $state['target'],
                    'after' => $this->identity($target->fresh()), 'moved_ids' => $moved,
                    'batch_renames' => $renames, 'preview_fingerprint' => $state['fingerprint']],
            ]);
            DB::afterCommit(function (): void {
                Cache::forget('medicines_list');
                Cache::forget('active_medicine_categories');
                Cache::forget('expiring_medicines_count');
            });

            return $target->fresh();
        }, 5);
    }

    private function authorize(User $actor): void
    {
        if (! $actor->hasRole('clinic_admin') || ! $actor->can('manage_medicines') || (int) $actor->status !== 1 || $actor->archived_at !== null) {
            throw new AuthorizationException();
        }
    }

    private function state(string $kind, int $sourceId, int $targetId, bool $lock): array
    {
        if (! isset(self::MODELS[$kind]) || $sourceId === $targetId) {
            throw ValidationException::withMessages(['source_id' => 'Choose two different records of the same type.']);
        }
        $model = self::MODELS[$kind];
        if (! $model::supportsInventoryMerges()) {
            throw ValidationException::withMessages(['source_id' => 'The inventory merge update has not been applied to this database.']);
        }
        $query = $model::withMerged()->whereIn('id', [$sourceId, $targetId])->orderBy('id');
        $records = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('id');
        $source = $records->get($sourceId);
        $target = $records->get($targetId);
        if (! $source || ! $target || $source->merged_into_id !== null || $target->merged_into_id !== null) {
            throw ValidationException::withMessages(['preview_token' => 'One record is missing or already merged. Select active records and preview again.']);
        }
        $rows = [];
        $sourceLabelIds = [];
        if ($kind === 'medicine') {
            $source->load('generic');
            $target->load('generic');
            foreach (self::MEDICINE_REFERENCES as $table => $column) {
                $q = DB::table($table)->whereIn($column, [$sourceId, $targetId])->orderBy('id');
                $rows[$table] = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($row) => (array) $row)->all();
            }
            $q = DB::table('medicine_transactions')->whereIn('batch_id', array_column($rows['medicine_batches'], 'id'))->orderBy('id');
            $rows['medicine_transactions'] = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($row) => (array) $row)->all();
        } else {
            $q = $this->medicineLabels($kind, [$sourceId, $targetId], [$source->name, $target->name])->orderBy('id');
            $rows['medicines'] = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($row) => (array) $row)->all();
            // Use the transfer's SQL comparison, including the database's collation for legacy text labels.
            $q = $this->medicineLabels($kind, [$sourceId], [$source->name])->orderBy('id');
            $sourceLabelIds = ($lock ? $q->lockForUpdate() : $q)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if ($kind === 'category' && Schema::hasColumn('generics', 'category_id')) {
                $q = DB::table('generics')->whereIn('category_id', [$sourceId, $targetId])->orderBy('id');
                $rows['generics'] = ($lock ? $q->lockForUpdate() : $q)->get()->map(fn ($row) => (array) $row)->all();
            }
        }
        $sourceIdentity = $this->identity($source);
        $targetIdentity = $this->identity($target);
        $blockers = [];
        if ($kind === 'medicine') {
            foreach (['ingredient' => 'generic ingredient', 'uom' => 'unit'] as $field => $label) {
                if ($this->normalize($sourceIdentity[$field]) === '' || $this->normalize($sourceIdentity[$field]) !== $this->normalize($targetIdentity[$field])) {
                    $blockers[] = 'Both medicines must have the same recorded '.$label.'. Correct their details before merging.';
                }
            }
        }
        $plan = $kind === 'medicine' ? $this->batchPlan($rows['medicine_batches'], $source, $target) : [];
        $stock = [];
        if ($kind === 'medicine') {
            foreach (['source' => $source, 'target' => $target] as $label => $medicine) {
                $batches = array_filter($rows['medicine_batches'], fn (array $row) => (int) $row['medicine_id'] === $medicine->id);
                $usable = array_filter($batches, fn (array $row) => $row['quantity'] > 0
                    && ($row['expiration_date'] === null || $row['expiration_date'] >= now()->toDateString()));
                $stock[$label] = ['total' => $batches ? (int) array_sum(array_column($batches, 'quantity')) : (int) $medicine->available_quantity,
                    'usable' => $batches ? (int) array_sum(array_column($usable, 'quantity')) : (int) $medicine->available_quantity];
            }
        }

        return ['source_model' => $source, 'target_model' => $target, 'source' => $sourceIdentity, 'target' => $targetIdentity,
            'rows' => $rows, 'source_label_ids' => $sourceLabelIds, 'blockers' => $blockers, 'batch_plan' => $plan, 'stock' => $stock,
            'fingerprint' => hash('sha256', json_encode([$kind, $source->getAttributes(), $target->getAttributes(),
                $sourceIdentity, $targetIdentity, $rows, $sourceLabelIds, $plan, now()->toDateString()], JSON_THROW_ON_ERROR))];
    }

    private function identity(Model $model): array
    {
        $fields = ['id', 'name', 'brand_name', 'generic_name', 'category_id', 'category', 'category_name', 'generic_id', 'dosage',
            'uom', 'sku', 'quantity', 'available_quantity', 'baseline_quantity', 'is_active', 'updated_at'];
        $identity = Arr::only($model->getAttributes(), $fields);
        if ($model instanceof Medicine) {
            $identity['ingredient'] = trim((string) $model->generic_name) ?: (string) $model->generic?->name;
        }

        return $identity;
    }

    private function normalize(?string $text): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $text)));
    }

    private function medicineLabels(string $kind, array $ids, array $names): Builder
    {
        return DB::table('medicines')->where(function (Builder $query) use ($kind, $ids, $names): void {
            $query->whereIn($kind.'_id', $ids)->orWhere(function (Builder $legacy) use ($kind, $names): void {
                $legacy->whereNull($kind.'_id')->where(function (Builder $label) use ($kind, $names): void {
                    $label->whereIn($kind === 'category' ? 'category' : 'generic_name', $names);
                    if ($kind === 'category') {
                        $label->orWhereIn('category_name', $names);
                    }
                });
            });
        });
    }

    private function sourceRows(string $kind, string $table, array $rows, array $state): array
    {
        $sourceId = $state['source']['id'];
        if ($table === 'medicine_transactions') {
            $ids = array_column(array_filter($state['rows']['medicine_batches'], fn (array $row) => (int) $row['medicine_id'] === $sourceId), 'id');
            return array_values(array_filter($rows, fn (array $row) => in_array($row['batch_id'], $ids, true)));
        }
        if ($kind === 'medicine') {
            $column = self::MEDICINE_REFERENCES[$table];
            return array_values(array_filter($rows, fn (array $row) => (int) $row[$column] === $sourceId));
        }
        if ($table === 'medicines') {
            return array_values(array_filter($rows, fn (array $row) => in_array((int) $row['id'], $state['source_label_ids'], true)));
        }

        return array_values(array_filter($rows, fn (array $row) => (int) $row[$kind.'_id'] === $sourceId));
    }

    private function batchPlan(array $rows, Medicine $source, Medicine $target): array
    {
        foreach ([$source, $target] as $medicine) {
            if (! array_filter($rows, fn (array $row) => (int) $row['medicine_id'] === $medicine->id) && $medicine->available_quantity > 0) {
                $rows[] = ['id' => 0, 'medicine_id' => $medicine->id, 'batch_number' => 'OPENING-'.$medicine->id,
                    'quantity' => $medicine->available_quantity, 'dosage' => $medicine->dosage, 'expiration_date' => null];
            }
        }
        $key = fn (string $name) => mb_strtolower(Str::ascii(rtrim($name)));
        $reserved = [];
        foreach ($rows as $row) {
            if ((int) $row['medicine_id'] === $target->id) {
                $reserved[$key($row['batch_number'])] = true;
            }
        }
        $plan = [];
        foreach ($rows as $row) {
            if ((int) $row['medicine_id'] !== $source->id) {
                continue;
            }
            $after = $row['batch_number'];
            $attempt = 0;
            while (isset($reserved[$key($after)])) {
                $suffix = '-M'.$source->id.'-B'.$row['id'].($attempt ? '-'.$attempt : '');
                $after = mb_substr($row['batch_number'], 0, 100 - mb_strlen($suffix)).$suffix;
                $attempt++;
            }
            $reserved[$key($after)] = true;
            $plan[] = ['id' => (int) $row['id'], 'before' => $row['batch_number'], 'after' => $after,
                'quantity' => (int) $row['quantity'], 'dosage' => trim((string) $row['dosage']) ?: ($source->dosage ?: 'N/A'),
                'expiry' => $row['expiration_date']];
        }

        return $plan;
    }
}
