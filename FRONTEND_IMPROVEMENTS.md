# Frontend & Blade Template Improvements

## 1. Performance Issues

### Current Problems:

-   Multiple CSS/JS files loaded without optimization
-   No asset versioning or caching
-   Large bundle sizes
-   No lazy loading implementation

### Solutions:

#### 1. Asset Optimization:

```javascript
// webpack.mix.js improvements
const mix = require("laravel-mix");

mix.js("resources/js/app.js", "public/js")
    .sass("resources/sass/app.scss", "public/css")
    .options({
        processCssUrls: false,
    })
    .version() // Add versioning for cache busting
    .sourceMaps(false, "source-map");

// Production optimizations
if (mix.inProduction()) {
    mix.options({
        terser: {
            terserOptions: {
                compress: {
                    drop_console: true,
                },
            },
        },
    });
}
```

#### 2. Lazy Loading Components:

```php
// Create lazy-loaded Livewire components
@lazy
class AppointmentTable extends LivewireTableComponent
{
    public function placeholder()
    {
        return view('components.loading-skeleton');
    }
}
```

## 2. Blade Template Refactoring

### Issues:

-   Duplicate code across templates
-   Inline styles and scripts
-   No component reusability

### Solutions:

#### 1. Create Reusable Components:

##### Action Buttons Component:

```php
// resources/views/components/crud/action-buttons.blade.php
@props([
    'model',
    'routes' => [],
    'permissions' => []
])

<div class="d-flex align-items-center">
    @if(isset($routes['show']) && (!isset($permissions['show']) || can($permissions['show'])))
        <a href="{{ route($routes['show'], $model) }}"
           title="{{ __('messages.common.view') }}"
           class="btn px-1 text-info fs-3">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if(isset($routes['edit']) && (!isset($permissions['edit']) || can($permissions['edit'])))
        <a href="{{ route($routes['edit'], $model) }}"
           title="{{ __('messages.common.edit') }}"
           class="btn px-1 text-primary fs-3">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if(isset($routes['delete']) && (!isset($permissions['delete']) || can($permissions['delete'])))
        <button type="button"
                title="{{ __('messages.common.delete') }}"
                class="btn px-1 text-danger fs-3 delete-btn"
                data-url="{{ route($routes['delete'], $model) }}">
            <i class="fas fa-trash"></i>
        </button>
    @endif
</div>
```

##### Form Field Component:

```php
// resources/views/components/form/input.blade.php
@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'value' => '',
    'placeholder' => ''
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $name }}"
           class="form-control @error($name) is-invalid @enderror"
           value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}"
           @if($required) required @endif>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

##### Modal Component:

```php
// resources/views/components/modal.blade.php
@props([
    'id',
    'title',
    'size' => 'md',
    'footer' => true
])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if($footer)
                <div class="modal-footer">
                    {{ $footer ?? '' }}
                </div>
            @endif
        </div>
    </div>
</div>
```

#### 2. Layout Improvements:

##### Master Layout Optimization:

```php
// resources/views/layouts/app.blade.php improvements
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    {{-- SEO Meta Tags --}}
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <meta name="description" content="@yield('description', 'NORSU Clinic Management System')">

    {{-- Preload critical resources --}}
    <link rel="preload" href="{{ mix('css/app.css') }}" as="style">
    <link rel="preload" href="{{ mix('js/app.js') }}" as="script">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset(getAppFavicon()) }}" type="image/png">

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    @stack('styles')

    {{-- Livewire Styles --}}
    @livewireStyles
</head>
<body class="@if(auth()->user()?->dark_mode) dark-mode @endif">
    <div id="app">
        {{-- Navigation --}}
        @include('layouts.navigation')

        {{-- Main Content --}}
        <main class="main-content">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('layouts.footer')
    </div>

    {{-- Scripts --}}
    <script src="{{ mix('js/app.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
```

## 3. JavaScript Improvements

### Issues:

-   Inline JavaScript in Blade templates
-   No proper error handling
-   Repeated AJAX code

### Solutions:

#### 1. Centralized JavaScript Classes:

```javascript
// resources/js/classes/ApiClient.js
class ApiClient {
    constructor() {
        this.baseURL = window.location.origin;
        this.csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
    }

    async request(url, options = {}) {
        const config = {
            headers: {
                "X-CSRF-TOKEN": this.csrfToken,
                Accept: "application/json",
                "Content-Type": "application/json",
                ...options.headers,
            },
            ...options,
        };

        try {
            const response = await fetch(`${this.baseURL}${url}`, config);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error("API request failed:", error);
            this.handleError(error);
            throw error;
        }
    }

    handleError(error) {
        // Show user-friendly error message
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "An error occurred. Please try again.",
        });
    }
}

// Usage
const apiClient = new ApiClient();
```

#### 2. Form Handling Class:

```javascript
// resources/js/classes/FormHandler.js
class FormHandler {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        this.apiClient = new ApiClient();
        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener("submit", this.handleSubmit.bind(this));
        }
    }

    async handleSubmit(event) {
        event.preventDefault();

        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData);

        try {
            this.showLoading();
            const response = await this.apiClient.request(this.form.action, {
                method: this.form.method,
                body: JSON.stringify(data),
            });

            this.handleSuccess(response);
        } catch (error) {
            this.handleError(error);
        } finally {
            this.hideLoading();
        }
    }

    showLoading() {
        const submitBtn = this.form.querySelector('[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    hideLoading() {
        const submitBtn = this.form.querySelector('[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.innerHTML = submitBtn.dataset.originalText || "Submit";
    }
}
```

## 4. CSS/SCSS Organization

### Issues:

-   No proper CSS organization
-   Inline styles in templates
-   No design system consistency

### Solutions:

#### 1. SCSS Structure:

```scss
// resources/sass/app.scss
// Base
@import "base/variables";
@import "base/mixins";
@import "base/typography";

// Layout
@import "layout/header";
@import "layout/sidebar";
@import "layout/footer";

// Components
@import "components/buttons";
@import "components/forms";
@import "components/modals";
@import "components/cards";

// Pages
@import "pages/dashboard";
@import "pages/appointments";
@import "pages/patients";

// Utilities
@import "utilities/helpers";
```

#### 2. CSS Custom Properties:

```scss
// resources/sass/base/_variables.scss
:root {
    // Colors
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;

    // Spacing
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 3rem;

    // Typography
    --font-family-base: "Poppins", sans-serif;
    --font-size-base: 1rem;
    --line-height-base: 1.5;

    // Borders
    --border-radius: 0.375rem;
    --border-width: 1px;

    // Shadows
    --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
```
