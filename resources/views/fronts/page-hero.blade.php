{{-- The hero band at the top of the public pages: the page title and the breadcrumb back to the home page; the header over it
     turns light (its styles: fronts.page-hero-styles). Re-audit 2026-11-13, DRY: five pages wrote it out. --}}
<script>document.addEventListener('DOMContentLoaded',function(){var h=document.querySelector('.header');if(h)h.classList.add('page-header-fix');});</script>
<section class="page-hero">
    <div class="container position-relative" style="z-index:1">
        <div class="col-12 text-center">
            <h1 class="mb-3">{{ $title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('medical') }}">{{ __('messages.web.home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>
        </div>
    </div>
</section>
