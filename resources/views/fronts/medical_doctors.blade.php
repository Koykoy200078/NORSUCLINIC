@extends('fronts.layouts.app')
@section('front-title')
{{ __('messages.web.medical_doctors') }}
@endsection

@section('front-content')
<style>
@include('fronts.page-hero-styles')
.doctor-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.4s ease;overflow:hidden}
.doctor-card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(102,126,234,0.12)}
</style>
<div class="our-team-page">
    <!-- start hero section -->
    @include('fronts.page-hero', ['title' => __('messages.web.our_team')])
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
                                {{ $doctor->specializations->first()?->name }}
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