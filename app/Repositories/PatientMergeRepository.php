<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PatientMergeRepository
{
    use LogsActivity;

    private const REFERENCES = ['document_issuances' => 'user_id', 'lab_requests' => 'patient_user_id',
        'prescriptions' => 'patient_id', 'medicine_bills' => 'patient_id', 'patient_queues' => 'patient_id'];

    public static function ready(): bool
    {
        return Schema::hasColumn('patients', 'merged_into_id') && Schema::hasColumn('addresses', 'merged_from_patient_id');
    }

    public function candidates(): array
    {
        $groups = [];
        foreach (Patient::with('user')->whereHas('user', fn ($q) => $q->where('type', User::PATIENT)->where('status', 1))->orderBy('id')->get() as $patient) {
            $user = $patient->user;
            $name = $this->normalize($user->first_name).' '.$this->normalize($user->last_name);
            $keys = [];
            if (trim($user->first_name ?? '') !== '' && trim($user->last_name ?? '') !== '' && $user->dob && $user->gender !== null) {
                $keys['Name, birth date and sex'] = [$name.'|'.substr($user->dob, 0, 10).'|'.$user->gender];
            }
            $keys['University ID'] = array_values(array_unique(array_filter([
                $this->normalize($user->university_id_number), $this->normalize($patient->patient_unique_id),
            ])));
            $phone = preg_replace('/\D/', '', (string) $user->contact);
            if (strlen($phone) === 11 && str_starts_with($phone, '09')) {
                $phone = substr($phone, 1);
            } elseif (strlen($phone) === 12 && str_starts_with($phone, '639')) {
                $phone = substr($phone, 2);
            }
            if (strlen($phone) >= 7) {
                $keys['Phone'] = [$phone];
            }
            foreach ($keys as $reason => $values) {
                foreach ($values as $key) {
                    $groups[$reason][$key][] = $patient;
                }
            }
        }
        $pairs = [];
        foreach ($groups as $reason => $sets) {
            foreach ($sets as $set) {
                $set = array_slice($set, 0, 101);
                foreach ($set as $i => $source) {
                    foreach (array_slice($set, $i + 1) as $target) {
                        $key = $source->id.':'.$target->id;
                        // Limit the suggestions, never the selectable patient list or a chosen pair's validation.
                        if (! isset($pairs[$key]) && count($pairs) >= 100) {
                            continue;
                        }
                        $pairs[$key] ??= ['source' => $source, 'target' => $target, 'reasons' => []];
                        if (! in_array($reason, $pairs[$key]['reasons'], true)) {
                            $pairs[$key]['reasons'][] = $reason;
                        }
                    }
                }
            }
        }

        return array_values($pairs);
    }

    public function preview(int $sourceId, int $targetId, User $actor): array
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($sourceId, $targetId, $actor): array {
            $state = $this->state($sourceId, $targetId, false);
            $token = $state['blockers'] ? null : Crypt::encryptString(json_encode([
                'source' => $sourceId, 'target' => $targetId, 'actor' => $actor->id,
                'expires' => now()->addMinutes(15)->timestamp, 'fingerprint' => $state['fingerprint'],
            ], JSON_THROW_ON_ERROR));
            $counts = [];
            foreach ($state['source_rows'] as $table => $rows) {
                $counts[$table] = count($rows);
            }
            $counts['consultation_images'] = array_sum(array_map(function (array $row): int {
                $images = json_decode($row['consultation_images'] ?? '[]', true);
                return is_array($images) ? count($images) : 0;
            }, $state['source_rows']['document_issuances']));

            return ['source' => $state['source'], 'target' => $state['target'], 'counts' => $counts,
                'blockers' => $state['blockers'], 'token' => $token];
        });
    }

    public function merge(int $sourceId, int $targetId, string $token, User $actor): Patient
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($sourceId, $targetId, $token, $actor): Patient {
            $state = $this->state($sourceId, $targetId, true);
            try {
                $approval = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
                $valid = $approval['source'] === $sourceId && $approval['target'] === $targetId && $approval['actor'] === $actor->id
                    && $approval['expires'] >= now()->timestamp && hash_equals($state['fingerprint'], $approval['fingerprint']) && ! $state['blockers'];
            } catch (\Throwable $error) {
                $valid = false;
            }
            if (! $valid) {
                throw ValidationException::withMessages(['preview_token' => 'The preview expired or these records changed. Preview the merge again.']);
            }
            $source = $state['source'];
            $target = $state['target'];
            foreach (self::REFERENCES as $table => $column) {
                DB::table($table)->where($column, str_contains($column, 'user_id') ? $source->user_id : $sourceId)
                    ->update([$column => str_contains($column, 'user_id') ? $target->user_id : $targetId]);
            }
            $addressIds = array_column($state['source_rows']['addresses'], 'id');
            DB::table('addresses')->whereIn('id', $addressIds)->update(['owner_type' => $target->getMorphClass(), 'owner_id' => $targetId]);
            DB::table('addresses')->whereIn('id', $addressIds)->whereNull('merged_from_patient_id')->update(['merged_from_patient_id' => $sourceId]);
            // The default media path is based on its immutable media ID. Moving ownership keeps every file and URL.
            foreach ($state['source_rows']['media'] as $media) {
                $properties = json_decode($media['custom_properties'], true) ?: [];
                $properties['merged_from_patient_id'] ??= $sourceId;
                DB::table('media')->where('id', $media['id'])->update(['model_type' => $target->getMorphClass(), 'model_id' => $targetId,
                    'collection_name' => 'merged_profiles', 'custom_properties' => json_encode($properties, JSON_THROW_ON_ERROR)]);
            }
            DB::table('patients')->where('id', $sourceId)->update(['merged_into_id' => $targetId, 'merged_at' => now(),
                'merged_by' => $actor->id, 'archived_at' => now(), 'updated_at' => now()]);
            DB::table('patients')->where('merged_into_id', $sourceId)->update(['merged_into_id' => $targetId]);
            DB::table('users')->where('id', $source->user_id)->update(['archived_at' => now(), 'updated_at' => now(), 'remember_token' => null]);
            PatientQueue::syncAttachment($target->user_id);
            $moved = array_map(fn (array $rows): array => array_column($rows, 'id'), $state['source_rows']);
            $audit = self::logActivity('patient_merged', 'Patient #'.$sourceId.' merged into #'.$targetId, [
                'subject_type' => Patient::class, 'subject_id' => $targetId,
                'properties' => ['source' => ['id' => $sourceId, 'user_id' => $source->user_id],
                    'target' => ['id' => $targetId, 'user_id' => $target->user_id], 'moved_ids' => $moved, 'preview_fingerprint' => $state['fingerprint']],
            ]);
            if (! $audit || ! $audit->exists) {
                throw new \RuntimeException('A patient merge requires an audit entry.');
            }
            DB::afterCommit(fn () => Patient::flushLookupCaches());

            return $target;
        }, 5);
    }

    private function authorize(User $actor): void
    {
        if (! $actor->hasRole('clinic_admin') || ! $actor->can('manage_patients') || ! $actor->status || $actor->trashed()) {
            throw new AuthorizationException();
        }
    }

    private function state(int $sourceId, int $targetId, bool $lock): array
    {
        if (! self::ready() || $sourceId === $targetId) {
            throw ValidationException::withMessages(['source_id' => 'Apply the patient merge database update and choose two different active patients.']);
        }
        $query = Patient::withTrashed()->whereIn('id', [$sourceId, $targetId])->orderBy('id');
        $patients = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('id');
        $query = User::withTrashed()->whereIn('id', $patients->pluck('user_id'))->orderBy('id');
        $users = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('id');
        foreach ([$sourceId, $targetId] as $id) {
            $patient = $patients->get($id);
            $user = $patient ? $users->get($patient->user_id) : null;
            if (! $patient || $patient->trashed() || $patient->merged_into_id || ! $user || $user->trashed() || ! $user->status
                || (int) $user->type !== User::PATIENT || $user->roles()->where('name', '!=', 'patient')->exists()
                || $user->doctor()->exists() || $user->staffProfile()->exists()) {
                throw ValidationException::withMessages(['source_id' => 'One record is missing, archived, already merged or is not an active patient-only account.']);
            }
            $patient->setRelation('user', $user);
        }
        $source = $patients[$sourceId];
        $target = $patients[$targetId];
        if ($source->user_id === $target->user_id) {
            throw ValidationException::withMessages(['source_id' => 'These profiles share one user account and need a separate data repair.']);
        }
        $rows = [];
        $sourceRows = [];
        foreach (self::REFERENCES as $table => $column) {
            $isUser = str_contains($column, 'user_id');
            $query = DB::table($table)->whereIn($column, $isUser ? [$source->user_id, $target->user_id] : [$sourceId, $targetId])->orderBy('id');
            $rows[$table] = ($lock ? $query->lockForUpdate() : $query)->get()->map(fn ($row) => (array) $row)->all();
            $sourceRows[$table] = array_values(array_filter($rows[$table], fn (array $row) => (int) $row[$column] === ($isUser ? $source->user_id : $sourceId)));
        }
        foreach (['addresses' => ['owner_type', 'owner_id'], 'media' => ['model_type', 'model_id']] as $table => [$type, $column]) {
            $query = DB::table($table)->where(function ($query) use ($type, $column, $source, $target): void {
                $query->where(fn ($q) => $q->where($type, $source->getMorphClass())->whereIn($column, [$source->id, $target->id]))
                    ->orWhere(fn ($q) => $q->where($type, $source->user->getMorphClass())->whereIn($column, [$source->user_id, $target->user_id]));
            })->orderBy('id');
            $rows[$table] = ($lock ? $query->lockForUpdate() : $query)->get()->map(fn ($row) => (array) $row)->all();
            $sourceRows[$table] = array_values(array_filter($rows[$table], fn (array $row) =>
                ($row[$type] === $source->getMorphClass() && (int) $row[$column] === $sourceId)
                || ($row[$type] === $source->user->getMorphClass() && (int) $row[$column] === $source->user_id)));
        }
        $open = array_filter($rows['patient_queues'], fn (array $row) => in_array($row['status'], PatientQueue::OPEN_STATUSES, true)
            && substr($row['created_at'], 0, 10) === today()->toDateString());

        return ['source' => $source, 'target' => $target, 'source_rows' => $sourceRows,
            'blockers' => count($open) > 1 ? ['Resolve the open queue entries first: the retained patient can have only one open queue entry today.'] : [],
            'fingerprint' => hash('sha256', json_encode([$source->getAttributes(), $target->getAttributes(),
                $source->user->getAttributes(), $target->user->getAttributes(), $rows, today()->toDateString()], JSON_THROW_ON_ERROR))];
    }

    private function normalize(?string $text): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $text)));
    }
}
