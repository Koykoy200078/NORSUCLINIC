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
$criticalUntil = $today->copy()->addDays(7)->toDateString();
$warningFrom = $today->copy()->addDays(8)->toDateString();
$warningUntil = $today->copy()->addDays(30)->toDateString();

$criticalCount = \App\Models\MedicineBatch::where('quantity', '>', 0)
->whereNotNull('expiration_date')
->whereDate('expiration_date', '>=', $today->toDateString())
->whereDate('expiration_date', '<=', $criticalUntil)
    ->distinct('medicine_id')
    ->count('medicine_id');

    $warningCount = \App\Models\MedicineBatch::where('quantity', '>', 0)
    ->whereNotNull('expiration_date')
    ->whereDate('expiration_date', '>=', $warningFrom)
    ->whereDate('expiration_date', '<=', $warningUntil)
        ->distinct('medicine_id')
        ->count('medicine_id');

        $hasBaselineColumn = \Illuminate\Support\Facades\Cache::remember('schema_has_baseline_qty', 3600, function () {
            return \Illuminate\Support\Facades\Schema::hasColumn('medicines', 'baseline_quantity');
        });
        $stableDenominatorSql = $hasBaselineColumn
        ? 'COALESCE(NULLIF(baseline_quantity, 0), NULLIF(reorder_level, 0), NULLIF(minimum_stock_alert, 0), 1)'
        : 'COALESCE(NULLIF(reorder_level, 0), NULLIF(minimum_stock_alert, 0), 1)';

        $lowStockCount = \App\Models\Medicine::where('available_quantity', '>', 0)
        ->where(function ($q) use ($stableDenominatorSql) {
        $q->whereRaw('minimum_stock_alert IS NOT NULL AND minimum_stock_alert > 0 AND available_quantity <= minimum_stock_alert')
            ->orWhereRaw("stock_alert_percentage IS NOT NULL AND stock_alert_percentage > 0 AND (available_quantity / NULLIF($stableDenominatorSql, 0) * 100) <= stock_alert_percentage");
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
                {{-- 6) Medicine Inventory Tracking                                      --}}
                {{-- 7) Medicine Dispensing Management                                   --}}
                {{-- 8) Laboratory & Medical Request Management                          --}}
                {{-- 9) Certificate Issuance                                             --}}
                {{-- 10) Report Generation                                               --}}
                {{-- 11) Notifications & Alerts                                          --}}
                {{-- 12) Settings                                                        --}}
                {{-- Keep the next sections in this exact order.                         --}}
                {{-- ===================================================================== --}}

                {{-- [ORDER 1] Dashboard - role-specific dashboard routes --}}
                @php $dashboardUrl = getDashboardURL(); @endphp
                <li class="nav-item {{ Request::is('admin/dashboard*', 'staff/dashboard*', 'doctors/dashboard*', 'patients/dashboard*') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ url($dashboardUrl) }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
                        <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
                    </a>
                </li>

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
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('patients.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-hospital-user"></i></span>
                        <span class="aside-menu-title">Patients</span>
                    </a>
                </li>

                {{-- [ORDER 3-A] Patient Queuing - Staff (requires manage_patients) --}}
                @if(isRole('staff'))
                <li class="nav-item {{ Request::is('staff/patient-queue*') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('patient-queue.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
                        <span class="aside-menu-title">Queue</span>
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
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('patient-queue.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
                        <span class="aside-menu-title">Queue</span>
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
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('patient-queue.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-clipboard-list"></i></span>
                        <span class="aside-menu-title">Queue</span>
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

                {{-- [ORDER 4] Consultations --}}
                @can('manage_request_documents')
                @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
                @php
                $documentIssuanceIndexRoute = getRouteByRole('document-issuances.index');
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
                @endphp
                <li class="nav-item {{ $isConsultationNavActive ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ $documentIssuanceIndexRoute . '?module=consultation' }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-notes-medical"></i>
                        </span>
                        <span class="aside-menu-title">Consultations</span>
                        @php $incompleteDocsCount = $_menuIncompleteDocsBadge; @endphp
                        @if($incompleteDocsCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $incompleteDocsCount }} consultation form(s) need Assessment/Plan">
                            <i class="fas fa-exclamation-triangle me-1" style="font-size: 0.6rem;"></i>{{ $incompleteDocsCount }}
                        </span>
                        @endif
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 5] Prescription Management --}}
                @can('manage_request_documents')
                @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
                @php
                $prescriptionIndexRoute = getRouteByRole('prescriptions.index');
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
                <li class="nav-item {{ $isPrescriptionNavActive ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ $prescriptionIndexRoute }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-file-prescription"></i>
                        </span>
                        <span class="aside-menu-title">Prescriptions</span>
                    </a>
                </li>
                @endif
                @endcan

                @can('manage_medicines')
                <li
                    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/categories*', 'admin/generics*', 'admin/medicines*', 'admin/stock-in*', 'admin/medicine-inventory-tracking*')) ||
        (isRole('staff') && Request::is('staff/categories*', 'staff/generics*', 'staff/medicines*', 'staff/stock-in*', 'staff/medicine-inventory-tracking*')) ||
        (isRole('doctor') && Request::is('doctors/categories*', 'doctors/generics*', 'doctors/medicines*', 'doctors/stock-in*', 'doctors/medicine-inventory-tracking*'))
    ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('medicine-inventory.index') }}">
                        <span class="aside-menu-icon me-3"><i class="fas fa-capsules"></i></span>
                        <span class="aside-menu-title">Inventory</span>
                        @php
                        $criticalCount = $_menuMedicineBadge['criticalCount'] ?? 0;
                        $warningCount = $_menuMedicineBadge['warningCount'] ?? 0;
                        $lowStockCount = $_menuMedicineBadge['lowStockCount'] ?? 0;
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
                    </a>
                </li>

                <li
                    class="nav-item {{ 
        (isRole('clinic_admin') && Request::is('admin/used-medicine*', 'admin/medicine-history*', 'admin/dispense-records*', 'admin/medicine-dispensing-management*')) ||
        (isRole('staff') && Request::is('staff/used-medicine*', 'staff/medicine-history*', 'staff/dispense-records*', 'staff/medicine-dispensing-management*')) ||
        (isRole('doctor') && Request::is('doctors/used-medicine*', 'doctors/medicine-history*', 'doctors/dispense-records*', 'doctors/medicine-dispensing-management*'))
    ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('medicine-dispensing.index') }}">
                        <span class="aside-menu-icon me-3"><i class="fas fa-notes-medical"></i></span>
                        <span class="aside-menu-title">Dispensing</span>
                        <span class="d-none">{{ __('messages.used_medicine.used_medicines') }}</span>
                        <span class="d-none">{{ __('messages.medicine_bills.medicine_bills') }}</span>
                    </a>
                </li>
                @endcan

                {{-- [ORDER 8] Laboratory & Medical Request Management --}}
                @can('manage_request_documents')
                @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
                @php
                $isLabRequestNavActive =
                (isRole('clinic_admin') && Request::is('admin/lab-requests*')) ||
                (isRole('staff')        && Request::is('staff/lab-requests*')) ||
                (isRole('doctor')       && Request::is('doctors/lab-requests*'));
                @endphp
                <li class="nav-item {{ $isLabRequestNavActive ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ getRouteByRole('lab-requests.index') }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-flask"></i>
                        </span>
                        <span class="aside-menu-title">Lab Requests</span>
                        <span class="d-none">Laboratory Medical Request Management</span>
                        <span class="d-none">Create Lab Request Record Details Track Status Print Form</span>
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 9] Certificate Issuance --}}
                @can('manage_request_documents')
                @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
                @php
                $isDocumentIssuancePath =
                (isRole('clinic_admin') && Request::is('admin/document-issuances*')) ||
                (isRole('staff') && Request::is('staff/document-issuances*')) ||
                (isRole('doctor') && Request::is('doctors/document-issuances*'));

                $activeDocumentModule = request()->query('module');
                $activeDocumentType = request()->query('document_type');

                $isCertificateNavActive = $isDocumentIssuancePath && (
                $activeDocumentModule === 'certificate' ||
                (!$activeDocumentModule && $activeDocumentType === 'medical_certificate')
                );
                $documentIssuanceIndexRoute = getRouteByRole('document-issuances.index');
                @endphp
                <li class="nav-item {{ $isCertificateNavActive ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ $documentIssuanceIndexRoute . '?module=certificate' }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-file-medical"></i>
                        </span>
                        <span class="aside-menu-title">Certificates</span>
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 10] Report Generation (Formerly Activity Logs) --}}
                @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
                <li class="nav-item {{ 
    (isRole('clinic_admin') && Request::is('admin/activity-logs*')) ||
    (isRole('staff') && Request::is('staff/activity-logs*')) ||
    (isRole('doctor') && Request::is('doctors/activity-logs*'))
? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('activity-logs.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-chart-line"></i></span>
                        <span class="aside-menu-title">Report Generation</span>
                    </a>
                </li>
                @endif

                {{-- [ORDER 11] Notifications & Alerts --}}
                @if(isRole('clinic_admin') || isRole('staff') || isRole('doctor'))
                @php 
                $isNotificationsActive = Request::is('*/activity-logs*') && (request()->query('tab') === 'inventory' || request()->query('tab') === 'logs');
                @endphp
                <li class="nav-item aside-item-collapse {{ $isNotificationsActive ? 'show collapse-submenu' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4 aside-collapse-btn" href="javascript:void(0);" aria-expanded="{{ $isNotificationsActive ? 'true' : 'false' }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-bell"></i></span>
                        <span class="aside-menu-title">Notifications & Alerts</span>
                        <span class="aside-menu-collapse-icon ms-auto"><i class="fas fa-chevron-right fs-8"></i></span>
                    </a>
                    <ul class="aside-submenu list-unstyled mb-0" style="display: {{ $isNotificationsActive ? 'block' : 'none' }};">
                        <li class="nav-item {{ Request::is('*/activity-logs*') && request()->query('status') === 'low_stock' ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('activity-logs.index') . '?tab=inventory&status=low_stock' }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-exclamation-triangle"></i></span>
                                <span class="aside-menu-title">Low stocks alert</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('medicine-inventory.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-calendar-times"></i></span>
                                <span class="aside-menu-title">Expiry medicine alert</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::is('*/activity-logs*') && request()->query('tab') === 'logs' ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('activity-logs.index') . '?tab=logs' }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-info-circle"></i></span>
                                <span class="aside-menu-title">System notifications</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                {{-- [ORDER 12] Settings --}}
                @can('manage_settings')
                @php 
                $isSettingsCollapseActive = Request::is('*/settings*', '*/roles*', '*/backups*', '*/staffs*', '*/doctors*', '*/specializations*', '*/cms*');
                @endphp
                <li class="nav-item aside-item-collapse {{ $isSettingsCollapseActive ? 'show collapse-submenu' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4 aside-collapse-btn" href="javascript:void(0);" aria-expanded="{{ $isSettingsCollapseActive ? 'true' : 'false' }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-cogs"></i></span>
                        <span class="aside-menu-title">Settings</span>
                        <span class="aside-menu-collapse-icon ms-auto"><i class="fas fa-chevron-right fs-8"></i></span>
                    </a>
                    <ul class="aside-submenu list-unstyled mb-0" style="display: {{ $isSettingsCollapseActive ? 'block' : 'none' }};">
                        {{-- User Management Items --}}
                        @can('manage_staff')
                        @if(isRole('clinic_admin'))
                        <li class="nav-item {{ Request::is('*/staffs*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ route('staffs.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-users"></i></span>
                                <span class="aside-menu-title">{{ __('messages.staffs') }}</span>
                            </a>
                        </li>
                        @endif
                        @endcan
                        @can('manage_doctors')
                        <li class="nav-item {{ Request::is('*/doctors*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('doctors.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-user-doctor"></i></span>
                                <span class="aside-menu-title">{{ __('messages.doctors') }}</span>
                            </a>
                        </li>
                        @endcan

                        {{-- Roles & Permissions --}}
                        @can('manage_roles')
                        <li class="nav-item {{ Request::is('*/roles*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('roles.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-user-tag"></i></span>
                                <span class="aside-menu-title">Manage User roles</span>
                            </a>
                        </li>
                        @endcan

                        {{-- Specializations --}}
                        @can('manage_specialties')
                        <li class="nav-item {{ Request::is('*/specializations*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('specializations.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-user-shield"></i></span>
                                <span class="aside-menu-title">{{ __('messages.specializations') }}</span>
                            </a>
                        </li>
                        @endcan

                        {{-- System Settings --}}
                        <li class="nav-item {{ Request::is('*/settings*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('setting.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-sliders-h"></i></span>
                                <span class="aside-menu-title">Manage system settings</span>
                            </a>
                        </li>

                        {{-- Front CMS --}}
                        @can('manage_front_cms')
                        <li class="nav-item {{ Request::is('*/cms*', '*/sliders*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('cms.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-tasks"></i></span>
                                <span class="aside-menu-title">{{ __('messages.front_cms') }}</span>
                            </a>
                        </li>
                        @endcan

                        {{-- Backup --}}
                        <li class="nav-item {{ Request::is('*/backups*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ route('backups.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-database"></i></span>
                                <span class="aside-menu-title">Backup data</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
