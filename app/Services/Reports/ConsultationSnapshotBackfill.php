<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Fills the id columns of existing consultations (campus_id, college_id, course_id, year_level_id, department_id,
 * office_id, patient_type_id) that were added for the ACCOMPLISHMENT REPORT.
 *
 * A consultation saved only the NAMES of the patient's campus / college / course / year level and the label of the
 * patient type. Each id is taken from the matching name; when the name matches nothing ("Unknown College") or was
 * never saved (department, office) the patient's current record is used. Only empty columns are filled, so it can
 * be run again at any time and never overwrites a value the clinic has already saved.
 */
class ConsultationSnapshotBackfill
{
    /** @var array<string, string> consultation name column => [lookup table, name column] */
    private const NAMED = [
        'campus' => ['campuses', 'campus_name', 'campus_id'],
        'college' => ['colleges', 'college_name', 'college_id'],
        'course' => ['courses', 'course_name', 'course_id'],
        'year_level' => ['year_levels', 'year_level_name', 'year_level_id'],
    ];

    /**
     * @return int number of consultations that received at least one id
     */
    public function run(): int
    {
        $lookups = [];
        foreach (self::NAMED as $nameColumn => [$table, $column]) {
            $lookups[$nameColumn] = DB::table($table)->useWritePdo()->pluck('id', $column)
                ->mapWithKeys(fn ($id, $name) => [$this->key($name) => (int) $id])->all();
        }

        $types = DB::table('patient_types')->useWritePdo()->pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [strtolower((string) $code) => (int) $id])->all();
        $updated = 0;

        DB::table('document_issuances')
            ->useWritePdo()
            ->where(function ($query) {
                foreach (['campus_id', 'college_id', 'course_id', 'year_level_id', 'department_id', 'office_id', 'patient_type_id'] as $column) {
                    $query->orWhereNull($column);
                }
            })
            ->orderBy('id')
            ->chunkById(200, function ($documents) use ($lookups, $types, &$updated) {
                $userIds = $documents->pluck('user_id')->filter()->unique()->all();
                $users = DB::table('users')->useWritePdo()->whereIn('id', $userIds)->get()->keyBy('id');
                $patientTypes = DB::table('patients')->useWritePdo()->whereIn('user_id', $userIds)->pluck('patient_type_id', 'user_id');

                foreach ($documents as $document) {
                    $user = $users->get($document->user_id);
                    $changes = [];

                    foreach (self::NAMED as $nameColumn => [, , $idColumn]) {
                        if ($document->{$idColumn} !== null) {
                            continue;
                        }

                        $id = $lookups[$nameColumn][$this->key($document->{$nameColumn})] ?? ($user->{$idColumn} ?? null);
                        if ($id !== null) {
                            $changes[$idColumn] = (int) $id;
                        }
                    }

                    foreach (['department_id', 'office_id'] as $idColumn) {
                        if ($document->{$idColumn} === null && ($user->{$idColumn} ?? null) !== null) {
                            $changes[$idColumn] = (int) $user->{$idColumn};
                        }
                    }

                    if ($document->patient_type_id === null) {
                        $typeId = $types[$this->typeCode($document->informant)] ?? null;
                        $typeId ??= $patientTypes->get($document->user_id);

                        if ($typeId !== null) {
                            $changes['patient_type_id'] = (int) $typeId;
                        }
                    }

                    if ($changes !== []) {
                        DB::table('document_issuances')->where('id', $document->id)->update($changes);
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    private function key(mixed $name): string
    {
        return mb_strtolower(trim((string) $name));
    }

    /** The saved label ("Student", "Faculty", "Dependent", ...) as a patient type code. */
    private function typeCode(mixed $informant): string
    {
        $code = strtolower(trim((string) $informant));

        return $code === 'dependent' ? 'guest' : $code;
    }
}
