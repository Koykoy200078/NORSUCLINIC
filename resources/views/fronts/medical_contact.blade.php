@extends('fronts.layouts.app')
@section('front-title')
{{ __('messages.web.medical_contact') }}
@endsection
@section('front-content')
<style>
.page-hero{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);position:relative;overflow:hidden;padding:8rem 0 4rem}
.page-hero::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.08) 1px,transparent 1px);background-size:50px 50px;opacity:0.3;animation:grain 8s steps(10) infinite}
@keyframes grain{0%,100%{transform:translate(0,0)}10%{transform:translate(-5%,-10%)}20%{transform:translate(-15%,5%)}30%{transform:translate(7%,-25%)}40%{transform:translate(-5%,25%)}50%{transform:translate(-15%,10%)}60%{transform:translate(15%,0%)}70%{transform:translate(0%,15%)}80%{transform:translate(3%,35%)}90%{transform:translate(-10%,10%)}}
.page-hero h1{color:#fff;font-weight:800;font-size:2.5rem}
.page-hero .breadcrumb-item a{color:rgba(255,255,255,0.8)!important}
.page-hero .breadcrumb-item.active{color:rgba(255,255,255,0.6)!important}
.page-hero .breadcrumb-item+.breadcrumb-item::before{color:rgba(255,255,255,0.5)!important}
.page-header-fix{position:absolute!important;top:0;left:0;right:0;z-index:1000;background:transparent!important}
.page-header-fix .nav-link{color:#fff!important;font-weight:500;opacity:0.95}
.page-header-fix .nav-link:hover{opacity:1}
.page-header-fix .navbar-toggler-icon{filter:invert(1)}
.page-header-fix .btn-outline-primary{background:#fff!important;color:#667eea!important;border-color:#fff!important;font-weight:700!important;box-shadow:0 4px 15px rgba(0,0,0,0.15)!important}
.page-header-fix .btn-outline-primary:hover{background:linear-gradient(135deg,#667eea,#764ba2)!important;color:#fff!important;border-color:transparent!important}
.premium-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.4s ease}
.premium-card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(102,126,234,0.12)}
.premium-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#667eea,#764ba2);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;box-shadow:0 8px 20px rgba(102,126,234,0.3)}
</style>
<script>document.addEventListener('DOMContentLoaded',function(){var h=document.querySelector('.header');if(h)h.classList.add('page-header-fix');});</script>
<div class="contact-page">
    <!-- start hero section -->
    <section class="page-hero">
        <div class="container position-relative" style="z-index:1">
            <div class="col-12 text-center">
                <h1 class="mb-3">
                    {{ __('messages.web.contact_us') }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('medical') }}">{{ __('messages.web.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"> {{ __('messages.web.contact_us') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
    <!-- end hero section -->

    <!-- start contact form section -->
    <section class="contact-section bg-secondary p-t-100 p-b-100">
        <div class="container">
            <div class="bg-white rounded-20 box-shadow main-box">
                <div class="row">
                    <div class="col-lg-3 col-md-4 d-none d-md-block">
                        <div class="card bg-primary contect-information">
                            <div class="card-body">
                                <h4 class="text-white mb-4 pb-2">
                                    {{ __('messages.web.contact_us_for_any_information') }}
                                </h4>
                                <div class="text-white">
                                    <h5 class="mb-3"> {{ __('messages.web.location') }}</h5>
                                    <p class="paragraph text-white"> {{ getSettingValue('address_one') }}</p>
                                </div>
                                <div class="text-white">
                                    <h5 class="mb-3">{{ __('messages.web.email') }} & {{ __('messages.web.phone') }}
                                    </h5>
                                    <a href=" mailto:{{ getSettingValue('email') }}"
                                        class="text-decoration-none text-white d-block">
                                        {{ getSettingValue('email') }}
                                    </a>
                                    <a href="tel:{{ \App\Support\PhilippinePhone::e164(getSettingValue('contact_no')) ?? getSettingValue('contact_no') }}"
                                        class="text-decoration-none text-white d-block">
                                        {{ formatPhilippinePhone(getSettingValue('contact_no')) }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-8 ps-md-0">
                        <div class="contact-info-message p-5 bg-light">
                            <h4 class="mb-4">{{ __('messages.web.contact_information') }}</h4>
                            <p class="mb-3">{{ __('messages.web.you_can_reach_us') }}</p>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> {{ getSettingValue('address_one') }}</li>
                                <li class="mb-2"><i class="fas fa-envelope me-2"></i> <a href="mailto:{{ getSettingValue('email') }}">{{ getSettingValue('email') }}</a></li>
                                <li class="mb-2"><i class="fas fa-phone me-2"></i> <a href="tel:{{ \App\Support\PhilippinePhone::e164(getSettingValue('contact_no')) ?? getSettingValue('contact_no') }}">{{ formatPhilippinePhone(getSettingValue('contact_no')) }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end contact form section -->

    <!-- start contact information section -->
    <section class="information-section p-t-100 p-b-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-4 col-md-6 d-flex align-items-stretch">
                    <div class="card mx-lg-2 flex-fill">
                        <div class="card-body d-flex flex-column">
                            <div class="contact-icon-box d-flex align-items-center justify-content-center mb-4">
                                <i class="fa-solid fa-phone text-primary fs-3"></i>
                            </div>
                            <h4 class="mb-3 pt-2"> {{ __('messages.user.contact_number') }}</h4>
                            <a href="tel:{{ \App\Support\PhilippinePhone::e164(getSettingValue('contact_no')) ?? getSettingValue('contact_no') }}"
                                class="text-decoration-none text-gray-100 d-block fw-light">
                                {{ formatPhilippinePhone(getSettingValue('contact_no')) }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 d-flex align-items-stretch mt-md-0 mt-4">
                    <div class="card mx-lg-2 flex-fill">
                        <div class="card-body d-flex flex-column">
                            <div class="contact-icon-box d-flex align-items-center justify-content-center mb-4">
                                <i class="fa-solid fa-envelope text-primary fs-3"></i>
                            </div>
                            <h4 class="mb-3 pt-2"> {{ __('messages.web.email_address') }}</h4>
                            <a href=" mailto:{{ getSettingValue('email') }}"
                                class="text-decoration-none text-gray-100 d-block fw-light">
                                {{ getSettingValue('email') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 d-flex align-items-stretch mt-xl-0 mt-4 pt-xl-0 pt-lg-3">
                    <div class="card mx-lg-2 flex-fill">
                        <div class="card-body d-flex flex-column">
                            <div class="contact-icon-box d-flex align-items-center justify-content-center mb-4">
                                <i class="fa-solid fa-location-dot text-primary fs-3"></i>
                            </div>
                            <h4 class="mb-3 pt-2">{{ __('messages.setting.address') }}</h4>
                            <p class="paragraph mb-0">
                                {{ getSettingValue('address_one') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end contact information section -->
</div>
@endsection