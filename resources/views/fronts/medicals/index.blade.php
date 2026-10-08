@extends('fronts.layouts.app')
@section('front-title')
{{ __('messages.web.medical') }}
@endsection
@section('front-content')
<style>
.landing-premium{--primary-gradient:linear-gradient(135deg,#667eea 0%,#764ba2 100%);--primary-color:#667eea;--primary-dark:#764ba2}
.landing-hero{background:var(--primary-gradient);position:relative;overflow:hidden;min-height:100vh;display:flex;align-items:center}
.landing-hero::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.08) 1px,transparent 1px);background-size:50px 50px;opacity:0.4;animation:grain 8s steps(10) infinite}
@keyframes grain{0%,100%{transform:translate(0,0)}10%{transform:translate(-5%,-10%)}20%{transform:translate(-15%,5%)}30%{transform:translate(7%,-25%)}40%{transform:translate(-5%,25%)}50%{transform:translate(-15%,10%)}60%{transform:translate(15%,0%)}70%{transform:translate(0%,15%)}80%{transform:translate(3%,35%)}90%{transform:translate(-10%,10%)}}
.landing-floating-shapes{position:absolute;width:100%;height:100%;overflow:hidden;pointer-events:none;z-index:0}
.landing-shape{position:absolute;border-radius:50%;background:rgba(255,255,255,0.06);animation:float 20s infinite ease-in-out}
.landing-shape-1{width:120px;height:120px;top:15%;left:8%;animation-delay:0s}
.landing-shape-2{width:80px;height:80px;top:65%;left:75%;animation-delay:-5s}
.landing-shape-3{width:160px;height:160px;top:30%;left:85%;animation-delay:-10s}
.landing-shape-4{width:60px;height:60px;top:80%;left:20%;animation-delay:-15s}
@keyframes float{0%,100%{transform:translate(0,0) rotate(0deg)}33%{transform:translate(30px,-30px) rotate(120deg)}66%{transform:translate(-20px,20px) rotate(240deg)}}
.landing-hero-content{position:relative;z-index:1}
.landing-hero h1{font-size:clamp(2.2rem,5vw,3.8rem);font-weight:800;line-height:1.15;color:#fff;text-shadow:0 2px 20px rgba(0,0,0,0.15)}
.landing-hero .hero-subtitle{font-size:1.15rem;color:rgba(255,255,255,0.85);font-weight:500;letter-spacing:0.5px}
.landing-hero .hero-badge{display:inline-flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.2);border-radius:50px;padding:0.5rem 1.25rem;color:#fff;font-size:0.9rem;font-weight:600;margin-bottom:1.5rem}
.landing-hero-img{position:relative;z-index:1;filter:drop-shadow(0 25px 50px rgba(0,0,0,0.25));max-height:420px;object-fit:contain}
.landing-btn-white{background:#fff;color:#667eea;border:none;padding:0.875rem 2rem;border-radius:14px;font-weight:700;font-size:1rem;transition:all 0.3s ease;box-shadow:0 10px 30px -5px rgba(0,0,0,0.2)}
.landing-btn-white:hover{transform:translateY(-3px);box-shadow:0 20px 40px -5px rgba(0,0,0,0.25);color:#764ba2}
.landing-btn-outline{background:transparent;color:#fff;border:2px solid rgba(255,255,255,0.4);padding:0.875rem 2rem;border-radius:14px;font-weight:700;font-size:1rem;transition:all 0.3s ease}
.landing-btn-outline:hover{background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.6);color:#fff}
.landing-features{background:#f8fafc;position:relative}
.landing-features::before{content:'';position:absolute;top:0;left:0;right:0;height:120px;background:linear-gradient(to bottom,#667eea,#f8fafc)}
.landing-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2.5rem 2rem;transition:all 0.4s ease;height:100%}
.landing-card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(102,126,234,0.12)}
.landing-card-icon{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;margin-bottom:1.5rem;box-shadow:0 10px 25px rgba(102,126,234,0.3)}
.landing-card h3{font-size:1.25rem;font-weight:700;color:#1e293b;margin-bottom:0.75rem}
.landing-card p{color:#64748b;font-size:0.95rem;line-height:1.65;margin-bottom:0}
.landing-stats{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);position:relative;overflow:hidden}
.landing-stats::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(255,255,255,0.08) 0%,transparent 50%)}
.stat-item h3{font-size:2.5rem;font-weight:800;color:#fff;margin-bottom:0.5rem}
.stat-item p{color:rgba(255,255,255,0.8);font-size:1rem;font-weight:500;margin-bottom:0}
.stat-divider{width:1px;height:60px;background:rgba(255,255,255,0.2)}
.landing-cta{background:#f8fafc}
.landing-cta-card{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:24px;padding:4rem 2rem;position:relative;overflow:hidden;text-align:center}
.landing-cta-card::before{content:'';position:absolute;width:300px;height:300px;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);top:-100px;right:-50px;border-radius:50%}
.landing-cta-card h2{color:#fff;font-size:2rem;font-weight:700;margin-bottom:1rem;position:relative;z-index:1}
.landing-cta-card p{color:rgba(255,255,255,0.85);font-size:1.1rem;margin-bottom:2rem;position:relative;z-index:1}
.landing-cta-btn{background:#fff;color:#667eea;border:none;padding:1rem 2.5rem;border-radius:14px;font-weight:700;font-size:1.05rem;transition:all 0.3s ease;position:relative;z-index:1;box-shadow:0 10px 30px rgba(0,0,0,0.15)}
.landing-cta-btn:hover{transform:translateY(-3px);box-shadow:0 20px 40px rgba(0,0,0,0.2);color:#764ba2}
@media(max-width:991px){.landing-hero{min-height:auto;padding:6rem 0 4rem}.stat-divider{display:none!important}.landing-hero h1{font-size:2rem}}
.page-header-fix{position:absolute!important;top:0;left:0;right:0;z-index:1000;background:transparent!important}
.page-header-fix .nav-link{color:#fff!important;font-weight:500;opacity:0.95}
.page-header-fix .nav-link:hover{opacity:1}
.page-header-fix .navbar-toggler-icon{filter:invert(1)}
.page-header-fix .btn-outline-primary{background:#fff!important;color:#667eea!important;border-color:#fff!important;font-weight:700!important;box-shadow:0 4px 15px rgba(0,0,0,0.15)!important}
.page-header-fix .btn-outline-primary:hover{background:linear-gradient(135deg,#667eea,#764ba2)!important;color:#fff!important;border-color:transparent!important;box-shadow:0 8px 25px rgba(102,126,234,0.4)!important}
</style>

<div class="landing-premium">
    <!-- Hero Section -->
    <section class="landing-hero">
        <div class="landing-floating-shapes">
            <div class="landing-shape landing-shape-1"></div>
            <div class="landing-shape landing-shape-2"></div>
            <div class="landing-shape landing-shape-3"></div>
            <div class="landing-shape landing-shape-4"></div>
        </div>
        <div class="container landing-hero-content py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 text-center text-lg-start">
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>{{ $sliders->title ?? __('messages.web.welcome_to_norsu_clinic') }}</span>
                    </div>
                    <h1 class="mb-4">
                        {{ $sliders->short_description ?? __('messages.web.your_health_our_priority') }}
                    </h1>
                    <p class="hero-subtitle mb-5">
                        Providing exceptional healthcare services to the NORSU community. Your well-being is our commitment.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                        @if(!getLogInUser())
                        <a href="{{ route('login') }}" class="landing-btn-white text-decoration-none">
                            <i class="fas fa-sign-in-alt me-2"></i>{{ __('messages.login') }}
                        </a>
                        @elseif($dashboardRoute = getDashboardRouteName())
                        {{-- Same rule as the header button: the signed-in role's own dashboard, never a fixed admin URL. --}}
                        <a href="{{ route($dashboardRoute) }}" class="landing-btn-white text-decoration-none">
                            <i class="fas fa-th-large me-2"></i>{{ __('messages.web.dashboard') }}
                        </a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="{{ $sliders ? $sliders->slider_image : asset('assets/image/norsu_logo.png') }}" alt="NORSU Clinic" class="landing-hero-img img-fluid" loading="lazy" />
                </div>
            </div>
        </div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var header = document.querySelector('.header');
    if(header) header.classList.add('page-header-fix');
});
</script>
@endsection