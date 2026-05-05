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

                {{-- [ORDER 1] Dashboard --}}
                <li class="nav-item {{ isModuleActive('dashboard') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getDashboardURL() }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa fa-digital-tachograph"></i></span>
                        <span class="aside-menu-title">{{ __('messages.dashboard') }}</span>
                    </a>
                </li>

                {{-- [ORDER 2] Patients / Patient Record Management --}}
                @can('manage_patients')
                @if(canStaffAccessModule('patients'))
                <li class="nav-item {{ isModuleActive('patients') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('patients.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-hospital-user"></i></span>
                        <span class="aside-menu-title">Patients</span>
                    </a>
                </li>
                @endif

                {{-- [ORDER 3] Patient Queuing --}}
                @if(canStaffAccessModule('queue'))
                <li class="nav-item {{ isModuleActive('patient-queue') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('patient-queue.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-users-line"></i></span>
                        <span class="aside-menu-title">Queue</span>
                        @php $queueData = $_menuQueueBadge; @endphp
                        @if($queueData && $queueData->total > 0)
                        @if(isRole('doctor') && $queueData->in_progress > 0)
                        <span class="badge bg-warning rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="Patient in progress">
                            <i class="fas fa-user-clock"></i>
                        </span>
                        @elseif($queueData->priority > 0)
                        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;" title="{{ $queueData->priority }} priority patient(s)">{{ $queueData->priority }}</span>
                        @else
                        <span class="badge bg-primary rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 20px;">{{ $queueData->total }}</span>
                        @endif
                        @endif
                    </a>
                </li>
                @endif
                @endcan

                {{-- END SIDEBAR ORDER LOCK (items below can be reordered independently) --}}

                {{-- [ORDER 4] Consultations --}}
                @can('manage_request_documents')
                @php $documentIssuancesRouteName = getRouteNameByRole('document-issuances.index'); @endphp
                @if(\Illuminate\Support\Facades\Route::has($documentIssuancesRouteName) && canStaffAccessModule('consultations'))
                <li class="nav-item {{ isModuleActive('consultations') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ route($documentIssuancesRouteName) . '?module=consultation' }}">
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
                @php $prescriptionsRouteName = getRouteNameByRole('prescriptions.index'); @endphp
                @if(\Illuminate\Support\Facades\Route::has($prescriptionsRouteName) && canStaffAccessModule('prescriptions'))
                <li class="nav-item {{ isModuleActive('prescriptions') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ route($prescriptionsRouteName) }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-file-prescription"></i>
                        </span>
                        <span class="aside-menu-title">Prescriptions</span>
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 6] Inventory --}}
                @can('manage_medicines')
                @if(canStaffAccessModule('inventory'))
                <li class="nav-item {{ isModuleActive('inventory') ? 'active' : '' }}">
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
                    </a>
                </li>
                @endif

                {{-- [ORDER 7] Dispensing --}}
                @if(canStaffAccessModule('dispensing'))
                <li class="nav-item {{ isModuleActive('dispensing') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('medicine-dispensing.index') }}">
                        <span class="aside-menu-icon me-3"><i class="fas fa-notes-medical"></i></span>
                        <span class="aside-menu-title">Dispensing</span>
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 8] Laboratory & Medical Request Management --}}
                @can('manage_request_documents')
                @php $labRequestsRouteName = getRouteNameByRole('lab-requests.index'); @endphp
                @if(\Illuminate\Support\Facades\Route::has($labRequestsRouteName) && canStaffAccessModule('lab_requests'))
                <li class="nav-item {{ isModuleActive('lab-requests') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ route($labRequestsRouteName) }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-flask"></i>
                        </span>
                        <span class="aside-menu-title">Lab Requests</span>
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 9] Certificate Issuance --}}
                @can('manage_request_documents')
                @if(\Illuminate\Support\Facades\Route::has($documentIssuancesRouteName) && canStaffAccessModule('certificates'))
                <li class="nav-item {{ isModuleActive('certificates') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page"
                        href="{{ route($documentIssuancesRouteName) . '?module=certificate' }}">
                        <span class="aside-menu-icon pe-3">
                            <i class="fa-solid fa-file-medical"></i>
                        </span>
                        <span class="aside-menu-title">Certificates</span>
                    </a>
                </li>
                @endif
                @endcan

                {{-- [ORDER 10] Report Generation --}}
                @if(isRole('clinic_admin') || isRole('doctor') || ((isRole('staff') || isRole('nurse')) && canStaffAccessModule('reports')))
                <li class="nav-item {{ isModuleActive('reports') ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4" aria-current="page" href="{{ getRouteByRole('activity-logs.index') }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-chart-line"></i></span>
                        <span class="aside-menu-title">Report Generation</span>
                    </a>
                </li>
                @endif

                {{-- [ORDER 11] Notifications & Alerts --}}
                @if(isRole('clinic_admin') || isRole('doctor') || ((isRole('staff') || isRole('nurse')) && canStaffAccessModule('notifications')))
                <li class="nav-item aside-item-collapse {{ isModuleActive('notifications') ? 'show collapse-submenu' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4 aside-collapse-btn" href="javascript:void(0);" aria-expanded="{{ isModuleActive('notifications') ? 'true' : 'false' }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-bell"></i></span>
                        <span class="aside-menu-title">Notifications & Alerts</span>
                        <span class="aside-menu-collapse-icon ms-auto"><i class="fas fa-chevron-right fs-8"></i></span>
                    </a>
                    <ul class="aside-submenu list-unstyled mb-0" style="display: {{ isModuleActive('notifications') ? 'block' : 'none' }};">
                        <li class="nav-item {{ Request::is('*/activity-logs*') && request()->query('status') === 'low_stock' ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('activity-logs.index') . '?tab=inventory&status=low_stock' }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-exclamation-triangle"></i></span>
                                <span class="aside-menu-title">Low stocks alert</span>
                            </a>
                        </li>
                        @if(canStaffAccessModule('inventory'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('medicine-inventory.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-calendar-times"></i></span>
                                <span class="aside-menu-title">Expiry medicine alert</span>
                            </a>
                        </li>
                        @endif
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
                @canany(['manage_settings', 'manage_staff', 'manage_doctors', 'manage_roles', 'manage_specialties', 'manage_front_cms', 'manage_countries', 'manage_states', 'manage_cities'])
                @if(! (isRole('staff') || isRole('nurse')) || canStaffAccessAnyModule(['settings', 'doctors', 'specializations', 'roles', 'countries', 'states', 'cities', 'cms']))
                <li class="nav-item aside-item-collapse {{ isModuleActive('settings') ? 'show collapse-submenu' : '' }}">
                    <a class="nav-link d-flex align-items-center py-4 aside-collapse-btn" href="javascript:void(0);" aria-expanded="{{ isModuleActive('settings') ? 'true' : 'false' }}">
                        <span class="aside-menu-icon pe-3"><i class="fas fa-cogs"></i></span>
                        <span class="aside-menu-title">Settings</span>
                        <span class="aside-menu-collapse-icon ms-auto"><i class="fas fa-chevron-right fs-8"></i></span>
                    </a>
                    <ul class="aside-submenu list-unstyled mb-0" style="display: {{ isModuleActive('settings') ? 'block' : 'none' }};">
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
                        @if(canStaffAccessModule('doctors'))
                        <li class="nav-item {{ Request::is('*/doctors*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('doctors.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-user-doctor"></i></span>
                                <span class="aside-menu-title">{{ __('messages.doctors') }}</span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        {{-- Roles & Permissions --}}
                        @can('manage_roles')
                        @if(canStaffAccessModule('roles'))
                        <li class="nav-item {{ Request::is('*/roles*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('roles.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-user-tag"></i></span>
                                <span class="aside-menu-title">Manage User roles</span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        {{-- Specializations --}}
                        @can('manage_specialties')
                        @if(canStaffAccessModule('specializations'))
                        <li class="nav-item {{ Request::is('*/specializations*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('specializations.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-user-shield"></i></span>
                                <span class="aside-menu-title">{{ __('messages.specializations') }}</span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        {{-- System Settings --}}
                        @can('manage_settings')
                        @if(canStaffAccessModule('settings'))
                        <li class="nav-item {{ Request::is('*/settings*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('setting.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-sliders-h"></i></span>
                                <span class="aside-menu-title">Manage system settings</span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        {{-- Front CMS --}}
                        @can('manage_front_cms')
                        @if(canStaffAccessModule('cms'))
                        <li class="nav-item {{ Request::is('*/cms*', '*/sliders*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ getRouteByRole('cms.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-tasks"></i></span>
                                <span class="aside-menu-title">{{ __('messages.front_cms') }}</span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        {{-- Backup --}}
                        @if(isRole('clinic_admin'))
                        <li class="nav-item {{ Request::is('*/backups*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center py-4 ps-10" href="{{ route('backups.index') }}">
                                <span class="aside-menu-icon pe-3"><i class="fas fa-database"></i></span>
                                <span class="aside-menu-title">Backup data</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                @endcanany