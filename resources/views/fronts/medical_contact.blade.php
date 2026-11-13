@extends('fronts.layouts.app')
@section('front-title')
{{ __('messages.web.medical_contact') }}
@endsection
@section('front-content')
<style>
@include('fronts.page-hero-styles')
.premium-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.4s ease}
.premium-card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(102,126,234,0.12)}
.premium-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#667eea,#764ba2);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;box-shadow:0 8px 20px rgba(102,126,234,0.3)}
</style>
<div class="contact-page">
    <!-- start hero section -->
    @include('fronts.page-hero', ['title' => __('messages.web.contact_us')])
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