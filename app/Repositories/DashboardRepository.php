<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * What the dashboards show: the headline figures and the "recent patients registration" list.
 *
 * Everything is read fresh (three indexed COUNT queries and one short list): a patient added a second ago is in it, and
 * the figures count the same records as the modules they summarise (Patients, Doctors), by the patient record's own date.
 */
class DashboardRepository
{
    /** The list shows the newest patients of a range up to this many; `total` says how many there are. */
    public const LIST_LIMIT = 50;

    /**
     * The figures of the admin and staff dashboards.
     *
     * @return array{totalDoctorCount: int, totalPatientCount: int, totalRegisteredPatientCount: int}
     */
    public function figures(): array
    {
        return [
            'totalDoctorCount' => User::where('type', User::DOCTOR)->where('status', User::ACTIVE)->count(),
            'totalPatientCount' => Patient::count(),
            'totalRegisteredPatientCount' => $this->registeredBetween($this->period('day'))->count(),
        ];
    }

    /**
     * The patients registered in a range, newest first, as plain rows for the screen and the Day / Week / Month tabs.
     *
     * @param  string|null  $range  day, week or month (this day / this Monday-to-Sunday week / this month of this year)
     * @param  string  $panel  admin or staff: decides the link of each row (null when the role cannot open the page)
     * @return array{data: array<int, array<string, mixed>>, total: int}
     */
    public function recentPatients(?string $range, string $panel): array
    {
        $period = $this->period($range);
        if ($period === null) {
            return ['data' => [], 'total' => 0];
        }

        $query = $this->registeredBetween($period);
        $total = (clone $query)->count();

        $rows = $query
            ->with(['user:id,first_name,last_name,email,university_id_number', 'media'])
            ->orderByDesc('patients.created_at')
            ->orderByDesc('patients.id')
            ->limit(self::LIST_LIMIT)
            ->get()
            ->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'patient_identifier' => $patient->user->university_id_number ?: $patient->patient_unique_id,
                'profile' => $patient->profile,
                'url' => $this->patientUrl($patient, $panel),
                'registered_label' => $patient->created_at->isoFormat('DD MMM YYYY hh:mm A'),
                'user' => [
                    'id' => $patient->user->id,
                    'full_name' => $patient->user->full_name,
                    'email' => $patient->user->email,
                ],
            ])
            ->all();

        return ['data' => $rows, 'total' => $total];
    }

    /** Active patient records (with a live account) created inside the period. */
    private function registeredBetween(?array $period): Builder
    {
        return Patient::query()->whereHas('user')->whereBetween('patients.created_at', $period);
    }

    /** @return array{0: Carbon, 1: Carbon}|null */
    private function period(?string $range): ?array
    {
        $now = Carbon::now();

        return match ($range) {
            'day' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(Carbon::MONDAY), $now->copy()->endOfWeek(Carbon::SUNDAY)],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => null,
        };
    }

    /** The patient page of the role, or null when the role may not open it (staff without Manage Patients). */
    private function patientUrl(Patient $patient, string $panel): ?string
    {
        return match ($panel) {
            'admin' => route('patients.show', $patient->id),
            'staff' => canUseModule('patients') ? route('staff.patients.show', $patient->id) : null,
            default => null,
        };
    }
}
