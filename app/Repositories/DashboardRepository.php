<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
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

        // Cache expensive queries for 5 minutes
        $cacheKey = 'admin_dashboard_data_' . $todayDate;

        $data = Cache::remember($cacheKey, 300, function () use ($todayDate) {
            $cachedData = [];

            // Optimized queries with proper indexing
            $cachedData['totalDoctorCount'] = User::where('type', User::DOCTOR)
                ->where('status', User::ACTIVE)
                ->count();

            $cachedData['totalPatientCount'] = User::where('type', User::PATIENT)->count();
            $cachedData['totalAppointmentCount'] = Appointment::count();

            $cachedData['todayAppointmentCount'] = Appointment::where('date', $todayDate)
                ->where('status', Appointment::BOOKED)
                ->count();

            $cachedData['totalRegisteredPatientCount'] = User::where('type', User::PATIENT)
                ->whereDate('created_at', $todayDate)
                ->count();

            $cachedData['upcomingAppointmentCount'] = Appointment::where('date', '>', $todayDate)->count();
            $cachedData['tomorrowAppointmentCount'] = Appointment::where('date', Carbon::tomorrow()->format('Y-m-d'))->count();

            // Use cached settings and optimized queries
            $cachedData['servicesArr'] = Cache::remember('active_services', 600, function () {
                return Service::where('status', true)->pluck('name', 'id')->toArray();
            });

            $cachedData['serviceCategoriesArr'] = Cache::remember('service_categories', 600, function () {
                return ServiceCategory::pluck('name', 'id')->toArray();
            });

            $cachedData['doctorArr'] = Cache::remember('doctors_list', 600, function () {
                return Doctor::with('user:id,first_name,last_name')->get()
                    ->pluck('user.full_name', 'id')->toArray();
            });

            return $cachedData;
        });

        // Get patients separately as they need fresh data
        $data['patients'] = Patient::with(['user:id,first_name,last_name', 'appointments:id,patient_id'])
            ->withCount('appointments')
            ->whereDate('created_at', $todayDate)
            ->orderBy('created_at', 'DESC')
            ->paginate(5);

        return $data;
    }

    //Doctor
    /**
     * @return mixed
     */
    public function getDoctorData()
    {
        $doctorId = getLogInUser()->doctor->id;
        $todayDate = Carbon::now()->format('Y-m-d');

        // Cache doctor stats for 5 minutes
        $cacheKey = "doctor_stats_{$doctorId}_{$todayDate}";

        $appointments = Cache::remember($cacheKey, 300, function () use ($doctorId, $todayDate) {
            return [
                'totalAppointmentCount' => Appointment::where('doctor_id', $doctorId)
                    ->whereNotIn('status', [Appointment::CANCELLED])
                    ->count(),
                'todayAppointmentCount' => Appointment::where('doctor_id', $doctorId)
                    ->where('date', $todayDate)
                    ->whereNotIn('status', [Appointment::CANCELLED])
                    ->count(),
                'upcomingAppointmentCount' => Appointment::where('doctor_id', $doctorId)
                    ->where('date', '>', $todayDate)
                    ->where('status', Appointment::BOOKED)
                    ->count(),
            ];
        });

        // Get today's appointments with optimized eager loading
        $appointments['records'] = Appointment::with(['patient.user:id,first_name,last_name'])
            ->where('doctor_id', $doctorId)
            ->where('status', Appointment::BOOKED)
            ->whereDate('date', Carbon::today())
            ->orderBy('date', 'ASC')
            ->paginate(5);

        return $appointments;
    }

    //admin
    public function patientData($input)
    {
        if (isset($input['day'])) {
            $data = Patient::with(['user', 'appointments'])
                ->withCount('appointments')
                ->whereRaw('Date(created_at) = CURDATE()')
                ->orderBy('created_at', 'DESC')
                ->paginate(5);

            return $data;
        }

        if (isset($input['week'])) {
            $now = Carbon::now();
            $weekStartDate = $now->startOfWeek()->format('Y-m-d H:i');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d H:i');
            $data = Patient::with(['user', 'appointments'])
                ->withCount('appointments')
                ->whereBetween('created_at', [$weekStartDate, $weekEndDate])
                ->orderBy('created_at', 'DESC')
                ->paginate(5);

            return $data;
        }

        if (isset($input['month'])) {
            $data = Patient::with(['user', 'appointments'])
                ->withCount('appointments')
                ->whereMonth('created_at', Carbon::now()->month)
                ->orderBy('created_at', 'DESC')
                ->paginate(5);

            return $data;
        }
    }

    //doctor
    /**
     * @return mixed
     */
    public function doctorAppointment($input)
    {
        $doctorId = getLogInUser()->doctor->id;

        if (isset($input['day'])) {

            $data = Appointment::with(['patient.user', 'user', 'services'])
                ->where('doctor_id', $doctorId)
                ->whereStatus(Appointment::BOOKED)
                ->whereDate('date', Carbon::today())
                ->orderBy('date', 'ASC')
                ->paginate(10);
            return $data;
        }

        if (isset($input['week'])) {
            $now = Carbon::now();
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');
            $data = Appointment::with(['patient.user', 'user', 'services'])
                ->where('doctor_id', $doctorId)
                ->whereStatus(Appointment::BOOKED)
                ->whereBetween('date', [$weekStartDate, $weekEndDate])
                ->orderBy('date', 'ASC')
                ->paginate(10);

            return $data;
        }

        if (isset($input['month'])) {
            $data = Appointment::with(['patient.user', 'user', 'services'])
                ->where('doctor_id', $doctorId)
                ->whereStatus(Appointment::BOOKED)
                ->whereMonth('date', Carbon::now()->month)
                ->orderBy('date', 'ASC')
                ->paginate(10);

            return $data;
        }
    }

    public function getPatientData(): array
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $patientId = getLogInUser()->patient->id;
        $todayCompleted = Appointment::wherePatientId($patientId)->where(
            'date',
            '=',
            $todayDate
        )->whereStatus(Appointment::FINISHED)->count();
        $data['todayAppointmentCount'] = Appointment::wherePatientId($patientId)->where(
            'date',
            '=',
            $todayDate
        )->count();
        $data['upcomingAppointmentCount'] = Appointment::wherePatientId($patientId)->where(
            'date',
            '>',
            $todayDate
        )->whereNotIn('status', [Appointment::CANCELLED])->count();
        $data['pastCompletedAppointmentCount'] = Appointment::wherePatientId($patientId)->where(
            'date',
            '<',
            $todayDate
        )->count();
        $data['completedAppointmentCount'] = $data['pastCompletedAppointmentCount'] + $todayCompleted;
        $data['todayAppointment'] = Appointment::with(['patient.user', 'doctor.user', 'services'])
            ->wherePatientId($patientId)
            ->whereStatus(Appointment::BOOKED)
            ->where('date', '=', $todayDate)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $data['upcomingAppointment'] = Appointment::with(['patient.user', 'doctor.user', 'services'])
            ->wherePatientId($patientId)
            ->whereStatus(Appointment::BOOKED)
            ->where('date', '>', $todayDate)
            ->paginate(10);

        return $data;
    }

    public function getAppointmentChartData($input): array
    {
        $appointments = Appointment::with('services')->whereYear('created_at', Carbon::now()->year)
            ->select(DB::raw('MONTH(created_at) as month,appointments.*'))->get();

        $transactions = Transaction::with(['user', 'appointment'])
            ->whereStatus('1')
            ->select(DB::raw('MONTH(created_at) as month,transactions.*'))
            ->get();

        $months = [
            1 => __('messages.months.jan'),
            2 => __('messages.months.feb'),
            3 => __('messages.months.mar'),
            4 => __('messages.months.apr'),
            5 => __('messages.months.may'),
            6 => __('messages.months.jun'),
            7 => __('messages.months.jul'),
            8 => __('messages.months.aug'),
            9 => __('messages.months.sep'),
            10 => __('messages.months.oct'),
            11 => __('messages.months.nov'),
            12 => __('messages.months.dec'),
        ];

        $monthWiseRecords = [];
        $monthWiseRecords1 = [];

        $serviceId = !empty($input['serviceId']) ? $input['serviceId'] : '';
        $doctorId = !empty($input['dashboardDoctorId']) ? $input['dashboardDoctorId'] : '';
        $serviceCategoryId = !empty($input['serviceCategoryId']) ? $input['serviceCategoryId'] : '';

        foreach ($months as $month => $monthName) {
            $monthWiseRecords[$monthName] = $appointments->where('month', $month)
                ->where('status', Appointment::FINISHED)
                ->when($serviceId, function ($query, $serviceId) {
                    return $query->where('service_id', $serviceId);
                })
                ->when($doctorId, function ($query, $doctorId) {
                    return $query->where('doctor_id', $doctorId);
                })
                ->when($serviceCategoryId, function ($query, $serviceCategoryId) {
                    return $query->where('services.category_id', $serviceCategoryId);
                })
                ->sum('payable_amount');
        }

        foreach ($months as $month => $monthName) {
            $monthWiseRecords1[$monthName] = $transactions->where('month', $month)

                ->when($serviceId, function ($query, $serviceId) {
                    return $query->where('appointment.service_id', $serviceId);
                })
                ->when($doctorId, function ($query, $doctorId) {
                    return $query->where('appointment.doctor_id', $doctorId);
                })
                ->when($serviceCategoryId, function ($query, $serviceCategoryId) {
                    return $query->where('appointment.services.category_id', $serviceCategoryId);
                })
                ->sum('amount');
        }

        return $monthWiseRecords1;
    }

    public function patientAllAppointment()
    {
        $patientId = getLogInUser()->patient->id;

        $patientappointments = Appointment::with(['patient.user', 'user', 'services'])
            ->where('patient_id', $patientId)
            ->whereStatus(Appointment::FINISHED)
            ->select(DB::raw('MONTH(date) as month,appointments.*'))->get();


        $transactions = Transaction::with(['user', 'appointment'])
            ->whereHas('appointment', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->whereStatus('1')
            ->select(DB::raw('MONTH(created_at) as month,transactions.*'))
            ->get();


        $months = [
            1 => __('messages.months.jan'),
            2 => __('messages.months.feb'),
            3 => __('messages.months.mar'),
            4 => __('messages.months.apr'),
            5 => __('messages.months.may'),
            6 => __('messages.months.jun'),
            7 => __('messages.months.jul'),
            8 => __('messages.months.aug'),
            9 => __('messages.months.sep'),
            10 => __('messages.months.oct'),
            11 => __('messages.months.nov'),
            12 => __('messages.months.dec'),
        ];

        $monthWiseRecords = [];
        $monthWiseRecords1 = [];


        foreach ($months as $month => $monthName) {
            $monthWiseRecords[$monthName] = $patientappointments->where('month', $month)
                ->sum('payable_amount');
        }

        foreach ($months as $month => $monthName) {
            $monthWiseRecords1[$monthName] = $transactions->where('month', $month)
                ->sum('amount');
        }

        return [$monthWiseRecords, $monthWiseRecords1];
    }

    public function doctorAllAppointment()
    {
        $doctorId = getLogInUser()->doctor->id;

        $doctorappointments = Appointment::with(['patient.user', 'user', 'services'])
            ->where('doctor_id', $doctorId)
            ->whereYear('created_at', Carbon::now()->year)
            ->select(DB::raw('MONTH(date) as month,appointments.*'))->get();

        $transactions = Transaction::with(['user', 'appointment'])
            ->whereHas('appointment', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->whereStatus('1')
            ->select(DB::raw('MONTH(created_at) as month,transactions.*'))
            ->get();

        $months = [
            1 => __('messages.months.jan'),
            2 => __('messages.months.feb'),
            3 => __('messages.months.mar'),
            4 => __('messages.months.apr'),
            5 => __('messages.months.may'),
            6 => __('messages.months.jun'),
            7 => __('messages.months.jul'),
            8 => __('messages.months.aug'),
            9 => __('messages.months.sep'),
            10 => __('messages.months.oct'),
            11 => __('messages.months.nov'),
            12 => __('messages.months.dec'),
        ];

        $monthWiseRecords1 = [];
        $monthWiseRecords2 = [];
        $monthWiseRecords3 = [];

        // foreach ($months as $month => $monthName) {
        //     $monthWiseRecords1[$monthName] = $doctorappointments->where('month', $month)
        //         ->count('id');
        // }
        // foreach ($months as $month => $monthName) {
        //     $monthWiseRecords2[$monthName] = $doctorappointments->where('month', $month)
        //         ->sum('payable_amount');
        // }
        // foreach ($months as $month => $monthName) {
        //     $monthWiseRecords3[$monthName] = $transactions->where('month', $month)
        //         ->sum('amount');
        // }

        // return [$monthWiseRecords2,$monthWiseRecords1,$monthWiseRecords3];
        foreach ($months as $month => $monthName) {
            $monthWiseRecords1[$monthName] = $doctorappointments->where('month', $month)
                ->count('id');
        }

        foreach ($months as $month => $monthName) {
            $monthWiseRecords2[$monthName] = 0; // Initialize to zero to handle cases where no records are found
            $records = $doctorappointments->where('month', $month)->pluck('payable_amount');
            foreach ($records as $record) {
                $monthWiseRecords2[$monthName] += floatval($record); // Convert to float before summing
            }
        }

        foreach ($months as $month => $monthName) {
            $monthWiseRecords3[$monthName] = 0; // Initialize to zero
            $records = $transactions->where('month', $month)->pluck('amount');
            foreach ($records as $record) {
                $monthWiseRecords3[$monthName] += floatval($record); // Convert to float before summing
            }
        }

        return [$monthWiseRecords2, $monthWiseRecords1, $monthWiseRecords3];
    }

    /**
     * Staff Dashboard Data
     * @return array
     */
    public function getStaffData(): array
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        // Reuse admin dashboard cache since staff sees similar data
        $cacheKey = 'staff_dashboard_data_' . $todayDate;

        $data = Cache::remember($cacheKey, 300, function () use ($todayDate) {
            $cachedData = [];

            // Optimized queries with proper indexing
            $cachedData['totalDoctorCount'] = User::where('type', User::DOCTOR)
                ->where('status', User::ACTIVE)
                ->count();

            $cachedData['totalPatientCount'] = User::where('type', User::PATIENT)->count();
            $cachedData['totalAppointmentCount'] = Appointment::count();

            $cachedData['todayAppointmentCount'] = Appointment::where('date', $todayDate)
                ->where('status', Appointment::BOOKED)
                ->count();

            $cachedData['totalRegisteredPatientCount'] = User::where('type', User::PATIENT)
                ->whereDate('created_at', $todayDate)
                ->count();

            $cachedData['upcomingAppointmentCount'] = Appointment::where('date', '>', $todayDate)->count();
            $cachedData['tomorrowAppointmentCount'] = Appointment::where('date', Carbon::tomorrow()->format('Y-m-d'))->count();

            // Reuse cached lookups
            $cachedData['servicesArr'] = Cache::remember('active_services', 600, function () {
                return Service::where('status', true)->pluck('name', 'id')->toArray();
            });

            $cachedData['serviceCategoriesArr'] = Cache::remember('service_categories', 600, function () {
                return ServiceCategory::pluck('name', 'id')->toArray();
            });

            $cachedData['doctorArr'] = Cache::remember('doctors_list', 600, function () {
                return Doctor::with('user:id,first_name,last_name')->get()
                    ->pluck('user.full_name', 'id')->toArray();
            });

            return $cachedData;
        });

        // Get patients separately as they need fresh data
        $data['patients'] = Patient::with(['user:id,first_name,last_name', 'appointments:id,patient_id'])
            ->withCount('appointments')
            ->whereDate('created_at', $todayDate)
            ->orderBy('created_at', 'DESC')
            ->paginate(5);

        return $data;
    }
}
