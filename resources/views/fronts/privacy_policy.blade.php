@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.privacy_policy') }}
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
.content-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:3rem}
</style>
<script>document.addEventListener('DOMContentLoaded',function(){var h=document.querySelector('.header');if(h)h.classList.add('page-header-fix');});</script>
    <section class="page-hero">
        <div class="container position-relative" style="z-index:1">
            <div class="col-12 text-center">
                <h1 class="mb-3">{{ __('messages.privacy_policy') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('medical') }}">{{ __('messages.web.home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.privacy_policy') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="content-card">{!! ($privacyPolicy['privacy_policy'] ?? '') !!}</div>
        </div>
    </section>
@endsection

