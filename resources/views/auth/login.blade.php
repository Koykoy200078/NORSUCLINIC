@extends('layouts.auth')
@section('title')
{{__('messages.login')}}
@endsection
@section('content')
<style>
.login-premium{min-height:100vh;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);position:relative;overflow:hidden}
.login-premium::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.1) 1px,transparent 1px);background-size:50px 50px;opacity:0.3;animation:grain 8s steps(10) infinite}
@keyframes grain{0%,100%{transform:translate(0,0)}10%{transform:translate(-5%,-10%)}20%{transform:translate(-15%,5%)}30%{transform:translate(7%,-25%)}40%{transform:translate(-5%,25%)}50%{transform:translate(-15%,10%)}60%{transform:translate(15%,0%)}70%{transform:translate(0%,15%)}80%{transform:translate(3%,35%)}90%{transform:translate(-10%,10%)}}
.login-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.3);border-radius:24px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25),0 0 0 1px rgba(255,255,255,0.1) inset;transition:transform 0.3s ease,box-shadow 0.3s ease}
.login-card:hover{transform:translateY(-2px);box-shadow:0 30px 60px -12px rgba(0,0,0,0.3),0 0 0 1px rgba(255,255,255,0.2) inset}
.form-floating-custom{position:relative;margin-bottom:1.5rem}
.form-floating-custom input{height:58px;border-radius:14px;border:2px solid #e2e8f0;padding:1rem 1rem 1rem 3rem;font-size:1rem;transition:all 0.3s ease;background:#f8fafc}
.form-floating-custom input:focus{border-color:#667eea;background:#fff;box-shadow:0 0 0 4px rgba(102,126,234,0.1);outline:none}
.form-floating-custom label{position:absolute;left:3rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:all 0.3s ease;font-size:1rem}
.form-floating-custom input:focus+label,.form-floating-custom input:not(:placeholder-shown)+label{top:0;left:1rem;transform:translateY(-50%) scale(0.85);background:#fff;padding:0 0.5rem;color:#667eea;font-weight:600}
.form-floating-custom .input-icon-left{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.1rem;transition:color 0.3s ease}
.form-floating-custom input:focus~.input-icon-left{color:#667eea}
.btn-premium{height:56px;border-radius:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;font-weight:700;font-size:1.05rem;letter-spacing:0.5px;transition:all 0.3s ease;position:relative;overflow:hidden}
.btn-premium::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.3),transparent);transition:left 0.5s ease}
.btn-premium:hover::before{left:100%}
.btn-premium:hover{transform:translateY(-2px);box-shadow:0 10px 30px -10px rgba(102,126,234,0.5)}
.brand-side{background:linear-gradient(135deg,rgba(102,126,234,0.1) 0%,rgba(118,75,162,0.1) 100%);border-radius:24px 0 0 24px;position:relative;overflow:hidden}
.brand-side::before{content:'';position:absolute;width:300px;height:300px;background:radial-gradient(circle,rgba(102,126,234,0.15) 0%,transparent 70%);top:-100px;right:-100px;border-radius:50%}
.brand-side::after{content:'';position:absolute;width:200px;height:200px;background:radial-gradient(circle,rgba(118,75,162,0.15) 0%,transparent 70%);bottom:-50px;left:-50px;border-radius:50%}
.floating-shapes{position:absolute;width:100%;height:100%;overflow:hidden;pointer-events:none}
.shape{position:absolute;border-radius:50%;background:rgba(255,255,255,0.05);animation:float 20s infinite ease-in-out}
.shape-1{width:80px;height:80px;top:10%;left:10%;animation-delay:0s}
.shape-2{width:120px;height:120px;top:70%;left:80%;animation-delay:-5s}
.shape-3{width:60px;height:60px;top:40%;left:60%;animation-delay:-10s}
.shape-4{width:100px;height:100px;top:80%;left:20%;animation-delay:-15s}
@keyframes float{0%,100%{transform:translate(0,0) rotate(0deg)}33%{transform:translate(30px,-30px) rotate(120deg)}66%{transform:translate(-20px,20px) rotate(240deg)}}
.password-toggle{position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;cursor:pointer;transition:color 0.3s ease;z-index:10}
.password-toggle:hover{color:#667eea}
</style>
<div class="login-premium d-flex align-items-center justify-content-center p-3 p-md-5">
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    <div class="container position-relative" style="z-index:1">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="login-card overflow-hidden">
                    <div class="row g-0">
                        <div class="col-lg-5 d-none d-lg-flex brand-side align-items-center justify-content-center p-5">
                            <div class="text-center position-relative" style="z-index:1">
                                <a href="{{ route('medical') }}" class="d-inline-block mb-4">
                                    <img alt="Logo" src="{{ asset(getAppLogo()) }}" class="img-fluid" style="width:120px;filter:drop-shadow(0 10px 20px rgba(102,126,234,0.3))" loading="lazy">
                                </a>
                                <h2 class="fw-bold text-dark mb-3" style="font-size:1.75rem">{{ getAppName() }}</h2>
                                <p class="text-muted mb-0 px-3" style="font-size:0.95rem;line-height:1.6">Your trusted partner in healthcare management. Secure, efficient, and always accessible.</p>
                                <div class="mt-4 d-flex justify-content-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:rgba(102,126,234,0.1);color:#667eea"><i class="fas fa-shield-alt"></i></span>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:rgba(102,126,234,0.1);color:#667eea"><i class="fas fa-user-md"></i></span>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:rgba(102,126,234,0.1);color:#667eea"><i class="fas fa-heartbeat"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 p-4 p-md-5">
                            <div class="d-lg-none text-center mb-4">
                                <a href="{{ route('medical') }}">
                                    <img alt="Logo" src="{{ asset(getAppLogo()) }}" class="img-fluid" style="width:80px" loading="lazy">
                                </a>
                            </div>
                            <div class="width-540 mx-auto">
                                @include('flash::message')
                                @include('layouts.errors')
                            </div>
                            <h2 class="fw-bold text-dark mb-1 text-center text-lg-start">{{ __('auth.sign_in') }}</h2>
                            <p class="text-muted mb-4 text-center text-lg-start">Welcome back! Please enter your credentials.</p>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-floating-custom">
                                    <i class="fas fa-envelope input-icon-left"></i>
                                    <input name="email" type="email" id="email" class="form-control" required placeholder=" " value="{{ old('email') }}">
                                    <label for="email">{{ __('messages.patient.email') }}</label>
                                </div>
                                <div class="form-floating-custom">
                                    <i class="fas fa-lock input-icon-left"></i>
                                    <input name="password" type="password" id="password" class="form-control" required placeholder=" ">
                                    <label for="password">{{ __('messages.patient.password') }}</label>
                                    <span class="password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye-slash" id="toggleIcon"></i>
                                    </span>
                                </div>
                                <button type="submit" class="btn btn-premium w-100 text-white">{{ __('messages.login') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
</script>
@endsection