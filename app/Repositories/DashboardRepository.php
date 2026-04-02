<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Class CityRepository
 *
 * @version July 31, 2021, 7:41 am UTC
 */
class DashboardRepository
{
    //admin
    public function getData(): array
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        $cacheKey = 'admin_dashboard_data_' . $todayDate;

        $data = Cache::remember($cacheKey, 300, function () use ($todayDate) {
            $cachedData = [];

            $cachedData['totalDoctorCount'] = User::where('type', User::DOCTOR)
                ->where('status', User::ACTIVE)
                ->count();

            $cachedData['totalPatientCount'] = User::where('type', User::PATIENT)->count();

            $cachedData['totalRegisteredPatientCount'] = User::where('type', User::PATIENT)
                ->whereDate('created_at', $todayDate)
                ->count();

            $cachedData['doctorArr'] = Cache::remember('doctors_list', 600, function () {
                return Doctor::with('user:id,first_name,last_name')->get()
                    ->pluck('user.full_name', 'id')->toArray();
            });

            return $cachedData;
        });

        $data['patients'] = Patient::with(['user:id,first_name,last_name'])
            ->whereDate('created_at', $todayDate)
            ->orderBy('created_at', 'DESC')
            ->paginate(5);

        return $data;
    }

    //Doctor
    public function getDoctorData(): array
    {
        return [];
    }

    //admin
    public function patientData($input)
    {
        if (isset($input['day'])) {
            return Patient::with(['user:id,first_name,last_name,email'])
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'DESC')
                ->paginate(5);
        }

        if (isset($input['week'])) {
            $now = Carbon::now();
            $weekStartDate = $now->copy()->startOfWeek()->format('Y-m-d H:i');
            $weekEndDate = $now->copy()->endOfWeek()->format('Y-m-d H:i');
            return Patient::with(['user:id,first_name,last_name,email'])
                ->whereBetween('created_at', [$weekStartDate, $weekEndDate])
                ->orderBy('created_at', 'DESC')
                ->paginate(5);
        }

        if (isset($input['month'])) {
            return Patient::with(['user:id,first_name,last_name,email'])
                ->whereMonth('created_at', Carbon::now()->month)
                ->orderBy('created_at', 'DESC')
                ->paginate(5);
        }
    }

    /**
     * Staff Dashboard Data
     * @return array
     */
    public function getStaffData(): array
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        $cacheKey = 'staff_dashboard_data_' . $todayDate;

        $data = Cache::remember($cacheKey, 300, function () use ($todayDate) {
            $cachedData = [];

            $cachedData['totalDoctorCount'] = User::where('type', User::DOCTOR)
                ->where('status', User::ACTIVE)
                ->count();

            $cachedData['totalPatientCount'] = User::where('type', User::PATIENT)->count();

            $cachedData['totalRegisteredPatientCount'] = User::where('type', User::PATIENT)
                ->whereDate('created_at', $todayDate)
                ->count();

            $cachedData['doctorArr'] = Cache::remember('doctors_list', 600, function () {
                return Doctor::with('user:id,first_name,last_name')->get()
                    ->pluck('user.full_name', 'id')->toArray();
            });

            return $cachedData;
        });

        $data['patients'] = Patient::with(['user:id,first_name,last_name'])
            ->whereDate('created_at', $todayDate)
            ->orderBy('created_at', 'DESC')
            ->paginate(5);

        return $data;
    }
}
