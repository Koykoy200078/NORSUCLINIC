{{-- The letterhead of the medical certificate form and of its part of the view page (re-audit 2026-11-13, DRY: two copies). --}}
<div class="flex items-center my-4">
    <!-- Left Logo -->
    <div>
        <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" class="w-28 h-28">
    </div>

    <!-- Text Content -->
    <div class="text-center flex-1">
        <h1 class="text-xl font-bold">{{ \App\Support\Letterhead::UNIVERSITY }}</h1>
        <h2 class="text-md">{{ \App\Support\Letterhead::ADDRESS }}</h2>
        <p class="text-sm">{{ \App\Support\Letterhead::PHONE }}</p>
    </div>

    <!-- Right Logo -->
    <div class="ml-4">
        <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" class="w-28 h-28">
    </div>
</div>
