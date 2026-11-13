@extends('fronts.layouts.app')
@section('front-title')
    {{ $title }}
@endsection
@section('front-content')
{{-- The Privacy Policy and Terms & Conditions pages (re-audit 2026-11-13, DRY: two copies of one page). $title is the page
     title; $content is the text the Front CMS keeps, purified when it is saved (CMSController::update). --}}
<style>
@include('fronts.page-hero-styles')
.content-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:3rem}
</style>
    @include('fronts.page-hero', ['title' => $title])
    <section class="py-5 bg-light">
        <div class="container">
            <div class="content-card">{!! $content !!}</div>
        </div>
    </section>
@endsection
