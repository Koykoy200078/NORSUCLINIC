<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PatientType extends Model
{
    use HasFactory;

    protected $table = 'patient_types';

    protected $fillable = [
        'code',
        'name',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'patient_type_id');
    }

    /**
     * The patient types for a select box in the clinic's order (student, faculty, staff, guest, then any other), the old
     * "dependent" type shown as "Guest". The patient form and the profile page use it (re-audit 2026-11-13, DRY: each
     * repository had a copy).
     *
     * @return Collection<int, string> id => the name shown
     */
    public static function selectOptions(): Collection
    {
        $order = ['student' => User::STUDENT, 'faculty' => User::FACULTY, 'staff' => User::EMPLOYEE, 'guest' => User::GUEST];

        return self::query()
            ->get(['id', 'code', 'name'])
            ->sortBy(fn (self $type) => $order[strtolower(trim((string) $type->code))] ?? 99)
            ->mapWithKeys(function (self $type) {
                $code = strtolower(trim((string) $type->code));
                $name = strtolower(trim((string) $type->name));

                return [$type->id => ($code === 'dependent' || $name === 'dependent') ? 'Guest' : $type->name];
            })
            ->toBase();
    }

    /**
     * The select box's options as id => "student" / "faculty" / "staff" / "guest" (the shown name in lower case,
     * "dependent" as guest), for the script that shows the fields of each kind of patient.
     *
     * @param  iterable<int|string, string>  $options  id => the name shown
     * @return array<int|string, string>
     */
    public static function scriptKeys(iterable $options): array
    {
        return collect($options)->mapWithKeys(function ($name, $id) {
            $key = strtolower(trim((string) $name));

            return [(string) $id => $key === 'dependent' ? 'guest' : $key];
        })->toArray();
    }
}
