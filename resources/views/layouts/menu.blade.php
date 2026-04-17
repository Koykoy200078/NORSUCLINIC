@php $styleCss = 'style' @endphp
@php
// -----------------------------------------------------------------------
// Sidebar badge data — computed ONCE per request with caching.
// queue: 30s TTL (near-real-time). medicine: 300s. docs: 60s.
// -----------------------------------------------------------------------
$_menuQueueBadge = \Illuminate\Support\Facades\Cache::remember('menu_badge_queue', 30, function () {
return \App\Models\PatientQueue::whereIn('status', ['waiting', 'in_progress'])
->selectRaw('COUNT(*) as total, SUM(is_priority) as priority, SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress')
->first();
});

$_menuMedicineBadge = \Illuminate\Support\Facades\Cache::remember('menu_badge_medicine', 300, function () {
$today = \Carbon\Carbon::now();
$criticalCount = \App\Models\PurchasedMedicine::whereNotNull('expiry_date')
->whereBetween('expiry_date', [$today, $today->copy()->addDays(7)])
->whereHas('medicines', fn($q) => $q->where('available_quantity', '>', 0))
->distinct('medicine_id')->count('medicine_id');
$warningCount = \App\Models\PurchasedMedicine::whereNotNull('expiry_date')
->whereBetween('expiry_date', [$today->copy()->addDays(8), $today->copy()->addDays(30)])
->whereHas('medicines', fn($q) => $q->where('available_quantity', '>', 0))
->distinct('medicine_id')->count('medicine_id');
$lowStockCount = \App\Models\Medicine::where('available_quantity', '>', 0)
->where(function ($q) {
$q->whereRaw('minimum_stock_alert IS NOT NULL AND available_quantity <= minimum_stock_alert')
    ->orWhereRaw('stock_alert_percentage IS NOT NULL AND quantity > 0 AND (available_quantity / quantity * 100) <= stock_alert_percentage');
        })->count();
        return compact('criticalCount', 'warningCount', 'lowStockCount');
        });

        $_menuIncompleteDocsBadge = \Illuminate\Support\Facades\Cache::remember('menu_badge_incomplete_docs', 60, function () {
        return \App\Models\DocumentIssuance::where('document_type', 'consultation_form')
        ->where(function ($q) {
        $q->whereNull('assessment')->orWhere('assessment', '')
        ->orWhereNull('plan')->orWhere('plan', '');
        })->count();
        });
        @endphp
        <div class="no-record text-center d-none">{{ __('messages.no_matching_records_found') }}</div>

        {{-- ===================================================================== --}}
        {{-- SIDEBAR ORDER LOCK (DO NOT REORDER)                                 --}}
        {{-- Applies to roles: clinic_admin, staff, doctor                       --}}
        {{-- 1) Dashboard                                                        --}}
        {{-- 2) Patients / Patient Record Management                             --}}
        {{-- 3) Patient Queuing                                                  --}}
        {{-- 4) Consultation Management                                          --}}
        {{-- 5) Prescription Management                                          --}}
        {{-- 6) Certificate Issuance                                             --}}
        {{-- Keep the next sections in this exact order.                         --}}
        {{-- ===================================================================== --}}

        {{-- [ORDER 1] Dashboard - role-specific dashboard routes --}}
        @if(isRole('clinic_admin'))
        {{-- Admin Dashboard --}}
        @can('manage_admin_dashboard')
        <li class="nav-item {{ Request::is('admin/dashboard*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('admin.dashboard') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
                <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
            </a>
        </li>
        @endcan
        @elseif(isRole('staff'))
        {{-- Staff Dashboard - Always accessible for staff users --}}
        <li class="nav-item {{ Request::is('staff/dashboard*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('staff.dashboard') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
                <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
            </a>
        </li>
        @elseif(isRole('doctor'))
        {{-- Doctor Dashboard - Only for doctor users --}}
        <li class="nav-item {{ Request::is('doctors/dashboard*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.dashboard') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
                <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
            </a>
        </li>
        @elseif(isRole('patient'))
        {{-- Patient Dashboard - Only for patient users --}}
        <li class="nav-item {{ Request::is('patients/dashboard*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('patients.dashboard') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
                <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
            </a>
        </li>
        @endif

        {{-- Live Consultations temporarily disabled for patients - route not implemented
@can('manage_live_consultations')
@if(isRole('patient'))
<li class="nav-item {{ Request::is('patients/live-consultation*') ? 'active' : '' }}">
        <a class="nav-link d-flex align-items-center py-4" aria-current="page"
            href="{{ route('patients.live-consultations.index') }}">
            <span class="aside-menu-icon pe-3"><i class="fas fa-video"></i></span>
            <span class="aside-menu-title">{{ __('messages.live_consultations') }}</span>
        </a>
        </li>
        @endif
        @endcan
        --}}

        {{-- [ORDER 2] Patients / Patient Record Management (clinic_admin/staff/doctor with manage_patients) --}}
        @php $isPrescriptionSelectionMode = request()->query('module') === 'prescription'; @endphp
        @can('manage_patients')
        <li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/patients*') && !$isPrescriptionSelectionMode) ||
    (isRole('staff') && Request::is('staff/patients*') && !$isPrescriptionSelectionMode) ||
    (isRole('doctor') && Request::is('doctors/patients*') && !$isPrescriptionSelectionMode)
? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('patients.index') : 
        (isRole('staff') ? route('staff.patients.index') : 
        (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
    }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-hospital-user"></i></span>
                <span class="aside-menu-title">Patients / Patient Record Management</span>
            </a>
        </li>

        {{-- [ORDER 3-A] Patient Queuing - Staff (requires manage_patients) --}}
        @if(isRole('staff'))
        <li class="nav-item {{ Request::is('staff/patient-queue*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('staff.patient-queue.index') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
                <span class="aside-menu-title">Patient Queuing</span>
                @php $queueData = $_menuQueueBadge; @endphp
                @if($queueData && $queueData->total > 0)
                @if($queueData->priority > 0)
                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $queueData->priority }} priority patient(s)">{{ $queueData->priority }}</span>
                @else
                <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $queueData->total }}</span>
                @endif
                @endif
                <span class="d-none">Queue Management</span>
            </a>
        </li>
        @endif

        {{-- [ORDER 3-B] Patient Queuing - Clinic Admin (requires manage_patients) --}}
        @if(isRole('clinic_admin'))
        <li class="nav-item {{ Request::is('admin/patient-queue*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('patient-queue.index') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
                <span class="aside-menu-title">Patient Queuing</span>
                @php $adminQueueData = $_menuQueueBadge; @endphp
                @if($adminQueueData && $adminQueueData->total > 0)
                @if($adminQueueData->priority > 0)
                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $adminQueueData->priority }} priority patient(s)">{{ $adminQueueData->priority }}</span>
                @else
                <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $adminQueueData->total }}</span>
                @endif
                @endif
                <span class="d-none">Queue Monitoring</span>
            </a>
        </li>
        @endif
        @endcan

        {{-- [ORDER 3-C] Patient Queuing - Doctor (doctor queue route) --}}
        @if(isRole('doctor'))
        <li class="nav-item {{ Request::is('doctors/patient-queue*') ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ route('doctors.patient-queue.index') }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
                <span class="aside-menu-title">Patient Queuing</span>
                @php $doctorQueueData = $_menuQueueBadge; @endphp
                @if($doctorQueueData && $doctorQueueData->total > 0)
                @if($doctorQueueData->in_progress > 0)
                <span class="badge bg-warning rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="Patient in progress">
                    <i class="fas fa-user-clock"></i>
                </span>
                @elseif($doctorQueueData->priority > 0)
                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $doctorQueueData->priority }} priority patient(s)">{{ $doctorQueueData->priority }}</span>
                @else
                <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $doctorQueueData->total }}</span>
                @endif
                @endif
            </a>
        </li>
        @endif

        {{-- END SIDEBAR ORDER LOCK (items below can be reordered independently) --}}

        @can('manage_request_documents')
        @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
        @php
        $documentIssuanceIndexRoute = isRole('clinic_admin') ? route('document-issuances.index') :
        (isRole('staff') ? route('staff.document-issuances.index') : route('doctors.document-issuances.index'));

        $isDocumentIssuancePath =
        (isRole('clinic_admin') && Request::is('admin/document-issuances*')) ||
        (isRole('staff') && Request::is('staff/document-issuances*')) ||
        (isRole('doctor') && Request::is('doctors/document-issuances*'));

        $activeDocumentModule = request()->query('module');
        $activeDocumentType = request()->query('document_type');

        $isConsultationNavActive = $isDocumentIssuancePath && (
        $activeDocumentModule === 'consultation' ||
        (!$activeDocumentModule && $activeDocumentType === 'consultation_form') ||
        (!$activeDocumentModule && !$activeDocumentType)
        );

        $isCertificateNavActive = $isDocumentIssuancePath && (
        $activeDocumentModule === 'certificate' ||
        (!$activeDocumentModule && $activeDocumentType === 'medical_certificate')
        );

        $prescriptionIndexRoute = isRole('clinic_admin') ? route('prescriptions.index') :
        (isRole('staff') ? route('staff.prescriptions.index') : route('doctors.prescriptions.index'));

        $isPrescriptionNavActive =
        (isRole('clinic_admin') && (
        Request::is('admin/prescriptions*', 'admin/prescription-medicine*', 'admin/prescription-pdf*', 'admin/patients/*/prescription-create') ||
        (Request::is('admin/patients*') && request()->query('module') === 'prescription')
        )) ||
        (isRole('staff') && (
        Request::is('staff/prescriptions*', 'staff/prescription-medicine*', 'staff/prescription-pdf*', 'staff/patients/*/prescription-create') ||
        (Request::is('staff/patients*') && request()->query('module') === 'prescription')
        )) ||
        (isRole('doctor') && (
        Request::is('doctors/prescriptions*', 'doctors/prescription-medicine*', 'doctors/prescription-pdf*', 'doctors/patients/*/prescription-create') ||
        (Request::is('doctors/patients*') && request()->query('module') === 'prescription')
        ));
        @endphp

        <li class="nav-item {{ $isConsultationNavActive ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                href="{{ $documentIssuanceIndexRoute . '?module=consultation' }}">
                <span class="aside-menu-icon pe-3">
                    <i class="fa-solid fa-notes-medical"></i>
                </span>
                <span class="aside-menu-title">Consultation Management</span>
                @php $incompleteDocsCount = $_menuIncompleteDocsBadge; @endphp
                @if($incompleteDocsCount > 0)
                <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $incompleteDocsCount }} consultation form(s) need Assessment/Plan">
                    <i class="fas fa-exclamation-triangle me-1" style="font-size: 0.6rem;"></i>{{ $incompleteDocsCount }}
                </span>
                @endif
                <span class="d-none">Record Walk-in or Schedule Visit</span>
                <span class="d-none">Encode Patient Complaints</span>
                <span class="d-none">Record Vital Signs and Findings</span>
                <span class="d-none">Assessment Plan Add Medicines to Plan Add Medicines to Nursing Intervention</span>
                <span class="d-none">Save Consultation Record</span>
            </a>
        </li>

        <li class="nav-item {{ $isPrescriptionNavActive ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                href="{{ $prescriptionIndexRoute }}">
                <span class="aside-menu-icon pe-3">
                    <i class="fa-solid fa-file-prescription"></i>
                </span>
                <span class="aside-menu-title">Prescription Management</span>
                <span class="d-none">Select patient then create prescription</span>
            </a>
        </li>

        <li class="nav-item {{ $isCertificateNavActive ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                href="{{ $documentIssuanceIndexRoute . '?module=certificate' }}">
                <span class="aside-menu-icon pe-3">
                    <i class="fa-solid fa-file-medical"></i>
                </span>
                <span class="aside-menu-title">Certificate Issuance</span>
                <span class="d-none">Medical Certificate</span>
            </a>
        </li>
        @endif
        @endcan

        @can('manage_medicines')
        <li
            class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/categories*', 'admin/generics*', 'admin/medicines*', 'admin/stock-in*', 'admin/used-medicine*', 'admin/medicine-history*')) ||
        (isRole('staff') && Request::is('staff/categories*', 'staff/generics*', 'staff/medicines*', 'staff/stock-in*', 'staff/used-medicine*', 'staff/medicine-history*')) ||
        (isRole('doctor') && Request::is('doctors/categories*', 'doctors/generics*', 'doctors/medicines*', 'doctors/stock-in*', 'doctors/used-medicine*', 'doctors/medicine-history*'))
    ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('medicines.index') : 
        (isRole('staff') ? route('staff.medicines.index') : 
        (isRole('doctor') ? route('doctors.medicines.index') : route('medicines.index')))
    }}">
                <span class="aside-menu-icon me-3"><i class="fas fa-capsules"></i></span>
                <span class="aside-menu-title">{{ __('messages.medicines') }}</span>
                @php
                $criticalCount = $_menuMedicineBadge['criticalCount'];
                $warningCount = $_menuMedicineBadge['warningCount'];
                $lowStockCount = $_menuMedicineBadge['lowStockCount'];
                @endphp

                <div class="d-flex align-items-center ms-auto gap-1">
                    @if($criticalCount > 0)
                    <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Critical: {{ $criticalCount }} medicine(s) expiring in 7 days or less">
                        <i class="fas fa-calendar-times me-1" style="font-size: 0.6rem;"></i>{{ $criticalCount }}
                    </span>
                    @elseif($warningCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Warning: {{ $warningCount }} medicine(s) expiring within 30 days">
                        <i class="fas fa-calendar-exclamation me-1" style="font-size: 0.6rem;"></i>{{ $warningCount }}
                    </span>
                    @endif

                    @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem; min-width: 20px;" title="Low Stock: {{ $lowStockCount }} medicine(s) below alert threshold">
                        <i class="fas fa-box-open me-1" style="font-size: 0.6rem;"></i>{{ $lowStockCount }}
                    </span>
                    @endif
                </div>
                <span class="d-none">{{ __('messages.medicine_categories') }}</span>
                <span class="d-none">{{ __('messages.medicine_brands') }}</span>
                <span class="d-none">{{ __('messages.medicines') }}</span>
                <span class="d-none">{{ __('messages.medicine_availability.medicine_availabilities') }}</span>
                <span class="d-none">{{ __('messages.used_medicine.used_medicines') }}</span>
                <span class="d-none">{{ __('messages.medicine_bills.medicine_bills') }}</span>
            </a>
        </li>
        @endcan
        @can('manage_specialties')
        <li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/specializations*')) ||
    (isRole('staff') && Request::is('staff/specializations*')) ||
    (isRole('doctor') && Request::is('doctors/specializations*'))
? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                href="{{ 
            isRole('clinic_admin') ? route('specializations.index') : 
            (isRole('staff') ? route('staff.specializations.index') : 
            (isRole('doctor') ? route('doctors.specializations.index') : route('specializations.index')))
        }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-user-shield"></i></span>
                <span class="aside-menu-title">{{ __('messages.specializations') }}</span>
            </a>
        </li>
        @endcan
        @can('manage_front_cms')
        <li
            class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/cms*', 'admin/sliders*', 'admin/front-medical-services*', 'admin/front-patient-testimonials*')) ||
        (isRole('staff') && Request::is('staff/cms*', 'staff/sliders*', 'staff/front-medical-services*', 'staff/front-patient-testimonials*'))
    ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('cms.index') : 
        (isRole('staff') ? route('staff.cms.index') : route('cms.index'))
    }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-tasks"></i></span>
                <span class="aside-menu-title">{{ __('messages.front_cms') }}</span>
                <span class="d-none">{{ __('messages.cms.cms') }}</span>
                <span class="d-none">{{ __('messages.sliders') }}</span>
            </a>
        </li>
        @endcan
        {{-- Activity Logs - For clinic_admin, staff, and doctor --}}
        @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
        <li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
    (isRole('staff') && Request::is('staff/activity-logs*')) ||
    (isRole('doctor') && Request::is('doctors/activity-logs*'))
? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('activity-logs.index') : 
        (isRole('staff') ? route('staff.activity-logs.index') : route('doctors.activity-logs.index'))
    }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
                <span class="aside-menu-title">Activity Logs</span>
            </a>
        </li>
        @endif

        {{-- User Managements - placed as second to last --}}
        @if((isRole('clinic_admin') && (auth()->user()->can('manage_staff') || auth()->user()->can('manage_doctors'))) || (isRole('staff') && auth()->user()->can('manage_doctors')))
        @php $isUserManagementActive = Request::is('admin/staffs*', 'admin/doctors*', 'staff/doctors*'); @endphp
        <li class="nav-item aside-item-collapse {{ $isUserManagementActive ? 'show collapse-submenu' : '' }}">
            <a class="nav-link d-flex align-items-center py-4 aside-collapse-btn" href="javascript:void(0);" aria-expanded="{{ $isUserManagementActive ? 'true' : 'false' }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-users-cog"></i></span>
                <span class="aside-menu-title">User Managements</span>
                <span class="aside-menu-collapse-icon ms-auto"><i class="fas fa-chevron-right fs-8"></i></span>
            </a>

            <ul class="aside-submenu list-unstyled mb-0">
                @can('manage_staff')
                @if(isRole('clinic_admin'))
                <li class="nav-item {{ Request::is('admin/staffs*') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4 ps-10" aria-current="page" href="{{ route('staffs.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-users"></i></span>
                        <span class="aside-menu-title">{{ __('messages.staffs') }}</span>
                    </a>
                </li>
                @endif
                @endcan

                @can('manage_doctors')
                @if(isRole('clinic_admin') || isRole('staff'))
                <li class="nav-item {{ (isRole('clinic_admin') && Request::is('admin/doctors*')) || (isRole('staff') && Request::is('staff/doctors*')) ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4 ps-10" aria-current="page" href="{{ isRole('clinic_admin') ? route('doctors.index') : route('staff.doctors.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fa-solid fa-user-doctor"></i></span>
                        <span class="aside-menu-title">{{ __('messages.doctors') }}</span>
                    </a>
                </li>
                @endif
                @endcan

            </ul>
        </li>
        @endif

        {{-- Settings - always last --}}
        @can('manage_settings')
        <li
            class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/settings*', 'admin/roles*', 'admin/countries*', 'admin/provinces*', 'admin/cities*')) ||
        (isRole('staff') && Request::is('staff/settings*', 'staff/roles*', 'staff/countries*', 'staff/provinces*', 'staff/cities*'))
    ? 'active' : '' }}">
            <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ 
        isRole('clinic_admin') ? route('setting.index') : 
        (isRole('staff') ? route('staff.setting.index') : route('setting.index'))
    }}">
                <span class="aside-menu-icon pe-3"><i class="fas fa-cogs"></i></span>
                <span class="aside-menu-title">{{ __('messages.settings') }}</span>
                <span class="d-none">{{ __('messages.settings') }}</span>
                <span class="d-none">{{ __('messages.roles') }}</span>
                <span class="d-none">{{ __('messages.countries') }}</span>
                <span class="d-none">{{ __('messages.states') }}</span>
                <span class="d-none">{{ __('messages.cities') }}</span>
                {{-- <span class="d-none">{{ __('messages.holiday.doctor_holiday') }}</span> --}}
            </a>
        </li>
        @endcan