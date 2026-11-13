{{-- The row above a page's content: the title on the left, the page's buttons (the slot) on the right. Without a title it
     shows the page's @section('title'). Re-audit 2026-11-13, DRY: fifteen pages wrote this wrapper out. --}}
@props(['title' => null])
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        @if ($title !== null)
        <h1 class="mb-0 me-1">{{ $title }}</h1>
        @else
        <h1 class="mb-0 me-1">@yield('title')</h1>
        @endif
        {{ $slot }}
    </div>
</div>
