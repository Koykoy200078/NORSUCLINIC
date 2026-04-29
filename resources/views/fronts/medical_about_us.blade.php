@extends('fronts.layouts.app')
@section('front-title')
{{ __('messages.web.medical_about_us') }}
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
</style>
<script>document.addEventListener('DOMContentLoaded',function(){var h=document.querySelector('.header');if(h)h.classList.add('page-header-fix');});</script>
<div class="about-page">
    <!-- start hero section -->
    <section class="page-hero">
        <div class="container position-relative" style="z-index:1">
            <div class="col-12 text-center">
                <h1 class="mb-3">
                    {{ __('messages.web.about_us') }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('medical') }}">{{ __('messages.web.home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.web.about_us') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
    <!-- end hero section -->

    <!-- start about section -->
    <section class="about-section p-b-100">
        <div class="container">
            <div class="row align-items-center flex-column-reverse flex-xl-row">
                <div class="col-xxl-6 col-xl-5 after-rectangle-shape position-relative about-left-content left-shape">
                    <div class="row position-relative z-index-1">
                        <div class="col-xl-6 col-md-3 about-block">
                            <div class="about-image-box rounded-20 bg-white">
                                <img src="{{ getSettingValue('about_image_2') }}" alt="About" class="rounded-20" loading="lazy" />
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-3 about-block">
                            <div class="about-image-box rounded-20 bg-white">
                                <img src="{{ getSettingValue('about_image_1') }}" alt="About" class="rounded-20" loading="lazy" />
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-3 about-block">
                            <div
                                class="about-content-box rounded-20 bg-white d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <h2 class="number-big text-primary">{{ getSettingValue('about_experience') }}</h2>
                                    <p class="mb-0">{{ __('messages.web.year_experience') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-3 about-block">
                            <div class="about-image-box bg-white rounded-20">
                                <img src="{{ getSettingValue('about_image_3') }}" alt="About" class="rounded-20" loading="lazy" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-xl-7">
                    <div class="about-right-content mb-md-5 mb-4 mb-xl-0 text-center text-xl-start">
                        <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.about_us') }}</h5>
                        <h2 class="pb-2">{{ getSettingValue('about_title') }}</h2>
                        <p class="paragraph pb-1">
                            {{ getSettingValue('about_short_description') }}
                        </p>
                        <ul class="d-flex ps-0 mb-4 pb-2 justify-content-center justify-content-xl-start flex-wrap">
                            <li class="mb-2">{{__('messages.web.emergency_help')}}</li>
                            <li class="mb-2">{{__('messages.web.qualified_doctors')}}</li>
                            <li class="mb-2">{{__('messages.web.best_professionals')}}</li>
                            <li class="mb-2">{{__('messages.web.medical_treatment')}}</li>
                        </ul>
                        <a href="{{ route('medicalContact') }}"
                            class="btn btn-primary ">{{__('messages.web.contact_us')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end about section -->

    <!-- start our-doctors section -->
    <section class="services-counter-section p-t-100 p-b-100">
        <div class="container">
            <div class="premium-card py-4 py-sm-0">
                <div class="row items-center justify-content-center">
                    <div class="col-xl-3 col-6 services-counter-block">
                        <div class="text-center my-4 my-sm-5 pipe">
                            <h4 class="text-primary fs-1 fw-bolder mb-3">{{ $data['specializationsCount'] }}</h4>
                            <h5 class="mb-0">{{ __('messages.specializations') }}</h5>
                        </div>
                    </div>
                    <div class="col-xl-3 col-6 services-counter-block">
                        <div class="text-center my-4 my-sm-5 pipe">
                            <h4 class="text-primary fs-1 fw-bolder mb-3">{{ $data['doctorsCount'] }}</h4>
                            <h5 class="mb-0">{{ __('messages.doctors') }}</h5>
                        </div>
                    </div>
                    <!-- <div class="col-xl-3 col-6 services-counter-block">
                        <div class="text-center my-4 my-sm-5 pipe">
                            <h4 class="text-primary fs-1 fw-bolder mb-3">{{ $data['patientsCount'] }}</h4>
                            <h5 class="mb-0">{{ __('messages.web.satisfied_patient') }}</h5>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </section>
    <!-- end our-doctors section -->

    <!-- start services counter section -->
    <section class="our-doctors-section bg-secondary p-t-100">
        <div class="container">
            <div class="text-center mb-5 pb-1">
                <h5 class="text-primary top-heading fs-6 mb-3">{{__('messages.web.our_doctor')}}</h5>
                <h2 class="pb-2 pb-5">{{__('messages.web.Meet_best_doctors')}}</h2>
            </div>
            <div class="row justify-content-center">
                @foreach($doctors as $doctor)
                <div class="col-xl-4 col-md-6 our-doctors-block d-flex align-items-stretch">
                    <div class="card mx-lg-2 flex-fill">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="card-image mb-4 rounded-circle">
                                <img src="{{ $doctor->user->profile_image }}" alt="NORSU LOGO" class="img-fluid rounded-circle object-image-cover" loading="lazy" />
                            </div>
                            <h4 class="text-primary"> {{ $doctor->user->full_name }}</h4>
                            <label class="designation-label pb-4 mb-3 d-block">
                                {{ $doctor->specializations->first()->name ?? 'General Practitioner' }}
                            </label>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- start services counter section -->
</div>

@endsection