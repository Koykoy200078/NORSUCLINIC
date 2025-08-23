# Code Quality & Architecture Improvements

## 1. Code Duplication Issues

### Identified Duplications:

#### Controller Logic:

-   Similar prescription handling across multiple controllers
-   Repeated patient/doctor fetching logic
-   Duplicate PDF generation code

#### Blade Templates:

-   Similar action buttons across different views
-   Repeated form structures
-   Common modals duplicated

### Solutions:

#### 1. Create Service Classes:

```php
// Create app/Services/PrescriptionService.php
class PrescriptionService
{
    public function createPrescription($data)
    {
        // Centralized prescription creation logic
    }

    public function generatePrescriptionPDF($prescription)
    {
        // Centralized PDF generation
    }
}
```

#### 2. Create Blade Components:

```php
// Create reusable action buttons component
// resources/views/components/action-buttons.blade.php
@props(['editRoute', 'deleteRoute', 'showRoute'])

<div class="d-flex align-items-center">
    <a href="{{ $showRoute }}" class="btn px-1 text-info fs-3">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ $editRoute }}" class="btn px-1 text-primary fs-3">
        <i class="fas fa-edit"></i>
    </a>
    <button class="btn px-1 text-danger fs-3" onclick="deleteRecord('{{ $deleteRoute }}')">
        <i class="fas fa-trash"></i>
    </button>
</div>
```

## 2. Controller Improvements

### Issues:

-   Controllers have too many responsibilities
-   Missing proper validation
-   No consistent error handling

### Solutions:

#### 1. Implement Single Responsibility:

```php
// Split large controllers into smaller, focused ones
class PatientController extends Controller
{
    // Only patient CRUD operations
}

class PatientAppointmentController extends Controller
{
    // Only patient appointment related operations
}
```

#### 2. Use Form Requests:

```php
// Create proper form request classes
php artisan make:request CreatePatientRequest
php artisan make:request UpdatePatientRequest
```

#### 3. Implement Consistent Response Structure:

```php
trait ApiResponseTrait
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}
```

## 3. Model Improvements

### Issues:

-   Relationships not properly defined
-   Missing model events
-   No proper scopes

### Solutions:

#### 1. Add Missing Relationships:

```php
// In Patient model
public function prescriptions()
{
    return $this->hasMany(Prescription::class);
}

public function medicineBills()
{
    return $this->hasMany(MedicineBill::class);
}
```

#### 2. Implement Model Scopes:

```php
// In User model
public function scopeActive($query)
{
    return $query->where('status', true);
}

public function scopeByRole($query, $role)
{
    return $query->whereHas('roles', function($q) use ($role) {
        $q->where('name', $role);
    });
}
```

## 4. Middleware Improvements

### Issues:

-   XSS middleware too simplistic
-   Missing rate limiting
-   No proper API versioning

### Solutions:

#### 1. Enhanced XSS Protection:

```php
use HTMLPurifier;

class XSSProtection
{
    public function handle($request, Closure $next)
    {
        $input = $request->all();
        $purifier = new HTMLPurifier();

        array_walk_recursive($input, function (&$input) use ($purifier) {
            $input = $purifier->purify($input);
        });

        $request->merge($input);
        return $next($request);
    }
}
```

#### 2. Rate Limiting:

```php
// In RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```
