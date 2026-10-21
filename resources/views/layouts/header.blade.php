<header class='d-flex align-items-center justify-content-between flex-grow-1 header px-3 px-xl-0'>
    <button type="button" class="btn px-0 aside-menu-container__aside-menubar d-block d-xl-none sidebar-btn">
        <i class="fa-solid fa-bars fs-1"></i>
    </button>
    <nav class="navbar navbar-expand-xl navbar-light {{(Auth()->user()->dark_mode) ? 'bg-light' : 'bg-white' }} top-navbar d-xl-flex d-block px-3 px-xl-0 py-4 py-xl-0 "
        id="nav-header">
        <div class="container-fluid">
            <div class="navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @include('layouts.sub_menu')
                </ul>
            </div>
        </div>
    </nav>
    <ul class="nav align-items-center">
        {{-- shown by resources/assets/js/realtime while the live connection is down; the page then refreshes every minute --}}
        <li class="px-2 d-none" id="live-updates-badge" title="Real-time updates are not connected. This page refreshes itself every minute until they are back.">
            <span class="badge bg-warning text-dark d-flex align-items-center"><i class="fas fa-plug me-1"></i> Live updates off</span>
        </li>
        @impersonating
        <li class="px-xxl-3 px-2">
            <span class="text-primary">
                <a data-turbo-eval="false" href="{{ route('impersonate.leave') }}">
                    <i class="fas fa-user-check fs-2"></i>
                </a>
            </span>
        </li>
        @endImpersonating
        {{-- <li class="px-sm-3 px-2">
            @if(Auth::user()->dark_mode)
                <a href="javascript:void(0)" title="Switch to Light mode"><i
                        class="fa-solid fa-moon text-primary fs-2 apply-dark-mode"></i></a>
            @else
                <a href="javascript:void(0)" title="Switch to Dark mode"><i
                        class="fa-solid fa-sun text-primary fs-2 apply-dark-mode"></i></a>
            @endif
        </li> --}}
        <li class="px-sm-3 px-2">
            @if(Auth::user()->dark_mode)
            <a href="javascript:void(0)" title="Switch to Light mode"><i
                    class="fa-solid fa-sun text-primary fs-2 apply-dark-mode"></i></a>
            @else
            <a href="javascript:void(0)" title="Switch to Dark mode"><i
                    class="fa-solid fa-moon text-primary fs-2 apply-dark-mode"></i></a>
            @endif
        </li>
        @include('layouts.notification-bell')

        <li class="px-sm-3 px-2">
            <div class="dropdown d-flex align-items-center py-4">
                <div class="image image-circle image-mini">
                    @if(getLogInUser()->hasRole('patient'))
                    <img class="img-fluid" alt="img-fluid"
                        src="{{ getLogInUser()->patient->profile }}" />
                    @elseif(getLogInUser()->hasRole('doctor'))
                    <img class="img-fluid" alt="img-fluid"
                        src="{{ getLogInUser()->profile_image }}" />
                    @else
                    <img class="img-fluid" alt="img-fluid"
                        src="{{ getLogInUser()->profile_image }}" />
                    @endif
                </div>
                <button class="btn dropdown-toggle ps-2 pe-0" type="button" id="menuDropDown"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    {{ getLogInUser()->full_name }}
                </button>
                <div class="dropdown-menu py-7 pb-4 my-2" aria-labelledby="menuDropDown">
                    <div class="text-center border-bottom pb-5">
                        <div class="image image-circle image-tiny mb-5">
                            @if(getLogInUser()->hasRole('patient'))
                            <img class="img-fluid" alt="img-fluid"
                                src="{{ getLogInUser()->patient->profile }}" />
                            @elseif(getLogInUser()->hasRole('doctor'))
                            <img class="img-fluid" alt="img-fluid"
                                src="{{ getLogInUser()->profile_image }}" />
                            @else
                            <img class="img-fluid" alt="img-fluid"
                                src="{{ getLogInUser()->profile_image }}" />
                            @endif
                        </div>
                        <h3 class="text-gray-900">{{ getLogInUser()->full_name }}</h3>
                        <h4 class="mb-0 fw-400 fs-6">{{ getLogInUser()->email }}</h4>
                    </div>
                    <ul class="pt-4">
                        <li>
                            <a class="dropdown-item text-gray-900" href="{{ route('profile.setting') }}">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                {{ __('messages.user.account_setting') }}
                            </a>
                        </li>
                        @if((is_impersonating() === false))
                        <li>
                            <a class="dropdown-item text-gray-900" id="changePassword" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                {{ __('messages.user.change_password') }}
                            </a>
                        </li>
                        @endif

                        <!-- @if(getLogInUser()->hasRole('doctor') || getLogInUser()->hasRole('patient'))
                        <li>
                            <a class="dropdown-item text-gray-900" id="emailNotification" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-bell"></i>
                                </span>
                                {{ __('messages.user.email_notification') }}
                            </a>
                        </li>
                        @endif -->

                        @if(session('impersonated_by'))
                        <li>
                            <a class="dropdown-item text-gray-900" href="{{ route('impersonate.leave') }}">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-user-check"></i>
                                </span>
                                {{ __('messages.user.return_to_admin') }}
                            </a>
                        </li>
                        @endif
                        <!-- <li>
                            <a class="dropdown-item text-gray-900" id="changeLanguage" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-globe"></i>
                                </span>
                                {{ __('messages.user.change_language') }}
                            </a>
                        </li> -->
                        <li>
                            <a class="dropdown-item text-gray-900 d-flex" href="javascript:void(0)">
                                <span class="dropdown-icon me-4 text-gray-600">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </span>
                                <form id="logout-form" action="{{ route('logout')}}" method="post">
                                    @csrf
                                </form>
                                <span onclick="event.preventDefault(); localStorage.clear();  document.getElementById('logout-form').submit();">
                                    {{ __('messages.user.sign_out') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </li>
        <li>
            <button type="button" class="btn px-0 d-block d-xl-none header-btn pb-2">
                <i class="fa-solid fa-bars fs-1"></i>
            </button>
        </li>
    </ul>
</header>
<div class="bg-overlay" id="nav-overly"></div>