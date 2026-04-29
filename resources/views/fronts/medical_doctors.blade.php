@extends('fronts.layouts.app')
@section('front-title')
{{ __('messages.web.medical_doctors') }}
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
.doctor-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.4s ease;overflow:hidden}
.doctor-card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(102,126,234,0.12)}
</style>
<script>document.addEventListener('DOMContentLoaded',function(){var h=document.querySelector('.header');if(h)h.classList.add('page-header-fix');});</script>
<div class="our-team-page">
    <!-- start hero section -->
    <section class="page-hero">
        <div class="container position-relative" style="z-index:1">
            <div class="col-12 text-center">
                <h1 class="mb-3">{{ __('messages.web.our_team') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('medical') }}">{{ __('messages.web.home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.web.our_team') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
    <!-- end hero section -->

    <!-- end our-team section -->
    <section class="our-team-section bg-secondary">
        <div class="container">
            <div class="row justify-content-center">
                @foreach($doctors as $doctor)
                <div class="col-xl-4 col-md-6 our-team-block d-flex align-items-stretch">
                    <div class="card mx-lg-2 flex-fill doctor-card">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="card-image mb-4 rounded-circle">
                                <img src="{{ $doctor->user->profile_image }}" alt="NORSU LOGO" class="img-fluid rounded-circle object-image-cover" loading="lazy" />
                            </div>
                            <h4 class="text-primary">{{ $doctor->user->full_name }}</h4>
                            <label class="designation-label pb-4 mb-3 d-block">
                                {{ $doctor->specializations->first()->name }}
                            </label>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- end our-team section -->
</div>
@endsection