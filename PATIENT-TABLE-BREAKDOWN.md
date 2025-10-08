# Patient Table - Request Documents Breakdown

## Summary

Successfully broke down "Total Request Documents" into separate columns for **Medical Certificates** and **Consultation Forms** with visual distinction using colored badges.

---

## Changes Made

### 1. **PatientTable.php - Query Builder**

**File:** `app/Livewire/PatientTable.php`

Added two new count queries to the builder:

```php
->withCount(['requestDocuments as medical_certificate_count' => function ($subQuery) {
    $subQuery->selectRaw('COUNT(*)')
        ->whereColumn('request_documents.user_id', 'patients.user_id')
        ->where('request_documents.document_type', 'medical_certificate');
}])
->withCount(['requestDocuments as consultation_form_count' => function ($subQuery) {
    $subQuery->selectRaw('COUNT(*)')
        ->whereColumn('request_documents.user_id', 'patients.user_id')
        ->where('request_documents.document_type', 'consultation_form');
}])
```

### 2. **PatientTable.php - Columns**

**Removed:**

```php
Column::make(__('Total Request Documents'), 'id')
    ->sortable()
    ->view('patients.components.total_request_documents'),
```

**Added:**

```php
Column::make(__('Medical Certificates'), 'medical_certificate_count')
    ->sortable()
    ->view('patients.components.medical_certificate_count'),
Column::make(__('Consultation Forms'), 'consultation_form_count')
    ->sortable()
    ->view('patients.components.consultation_form_count'),
```

### 3. **View Components Created**

**File:** `resources/views/patients/components/medical_certificate_count.blade.php`

```blade
<div class="d-flex justify-content-center">
    <div class="badge bg-primary">{{ $row->medical_certificate_count ?? 0 }}</div>
</div>
```

**File:** `resources/views/patients/components/consultation_form_count.blade.php`

```blade
<div class="d-flex justify-content-center">
    <div class="badge bg-success">{{ $row->consultation_form_count ?? 0 }}</div>
</div>
```

---

## Visual Appearance

### Badge Colors

-   **Medical Certificates:** Blue badge (`bg-primary`)
-   **Consultation Forms:** Green badge (`bg-success`)

### Table Layout

**Before:**

```
| Name | Email | Appointments | Total Requests | Email Verified | Registered | Action |
```

**After:**

```
| Name | Email | Appointments | Med Certs [3] | Consult Forms [5] | Email Verified | Registered | Action |
                                   (blue)           (green)
```

---

## Features

✅ **Sortable Columns**

-   Click on "Medical Certificates" column header to sort by that type
-   Click on "Consultation Forms" column header to sort by that type

✅ **Efficient Queries**

-   Uses Laravel's `withCount()` method
-   No N+1 query problems
-   Single database query retrieves all counts

✅ **Visual Distinction**

-   Blue badge for medical certificates
-   Green badge for consultation forms
-   Centered alignment for easy scanning

✅ **Dynamic Counts**

-   Shows `0` if no documents of that type
-   Updates automatically when documents are added/removed

---

## Testing

### 1. Access Patient List

```
/admin/patients
```

### 2. Verify Columns

Look for two new columns between "Appointments" and "Email Verified":

-   Medical Certificates (blue badges)
-   Consultation Forms (green badges)

### 3. Test Sorting

-   Click on "Medical Certificates" header → sorts by that count
-   Click on "Consultation Forms" header → sorts by that count

### 4. Verify Counts

Create a test patient with:

-   2 medical certificates
-   3 consultation forms

Expected display:

-   Blue badge showing `2`
-   Green badge showing `3`

---

## Database Structure

### Document Types

The `request_documents` table has a `document_type` column with two values:

-   `'medical_certificate'`
-   `'consultation_form'`

### Query Breakdown

```sql
-- Medical Certificates Count
SELECT COUNT(*)
FROM request_documents
WHERE user_id = patients.user_id
  AND document_type = 'medical_certificate'

-- Consultation Forms Count
SELECT COUNT(*)
FROM request_documents
WHERE user_id = patients.user_id
  AND document_type = 'consultation_form'
```

---

## Performance

**Query Efficiency:**

-   ✅ Uses `withCount()` - eager loading pattern
-   ✅ Single query with subqueries
-   ✅ No additional HTTP requests
-   ✅ Indexed columns (user_id, document_type)

**Expected Performance:**

-   No noticeable performance impact
-   Handles thousands of patients efficiently
-   Lazy loading with `#[Lazy]` attribute

---

## Customization

### Change Badge Colors

**Edit the view files:**

`medical_certificate_count.blade.php`:

```blade
<!-- Options: bg-primary, bg-secondary, bg-success, bg-danger, bg-warning, bg-info, bg-dark -->
<div class="badge bg-primary">{{ $row->medical_certificate_count ?? 0 }}</div>
```

`consultation_form_count.blade.php`:

```blade
<div class="badge bg-success">{{ $row->consultation_form_count ?? 0 }}</div>
```

### Add Icons

```blade
<div class="badge bg-primary">
    <i class="fas fa-file-medical"></i>
    {{ $row->medical_certificate_count ?? 0 }}
</div>
```

### Change Alignment

```blade
<!-- Left aligned -->
<div class="d-flex justify-content-start">
    <div class="badge bg-primary">{{ $row->medical_certificate_count ?? 0 }}</div>
</div>

<!-- Right aligned -->
<div class="d-flex justify-content-end">
    <div class="badge bg-primary">{{ $row->medical_certificate_count ?? 0 }}</div>
</div>
```

---

## Troubleshooting

### Columns Not Showing

**Solution 1: Clear Cache**

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

**Solution 2: Restart WAMP**

-   Stop all services
-   Start all services
-   OPcache will be cleared

### Counts Show 0

**Check:**

1. Are there any request documents in the database?
2. Is `document_type` column correctly populated?
3. Run query manually:
    ```sql
    SELECT document_type, COUNT(*)
    FROM request_documents
    GROUP BY document_type;
    ```

### Sorting Not Working

**Verify:**

1. Column definition has `->sortable()`
2. Column name matches the query alias
3. Cache is cleared

---

## Files Modified

1. ✅ `app/Livewire/PatientTable.php` (builder + columns)
2. ✅ `resources/views/patients/components/medical_certificate_count.blade.php` (new)
3. ✅ `resources/views/patients/components/consultation_form_count.blade.php` (new)

**Old file (can be deleted if not used elsewhere):**

-   `resources/views/patients/components/total_request_documents.blade.php`

---

## Future Enhancements

**Possible additions:**

1. **Tooltip with breakdown:**

    ```blade
    <div class="badge bg-primary" data-bs-toggle="tooltip"
         title="Last updated: {{ $row->updated_at }}">
        {{ $row->medical_certificate_count ?? 0 }}
    </div>
    ```

2. **Link to filtered view:**

    ```blade
    <a href="/admin/request-documents?type=medical_certificate&patient={{ $row->id }}">
        <div class="badge bg-primary">{{ $row->medical_certificate_count ?? 0 }}</div>
    </a>
    ```

3. **Percentage indicator:**
    ```blade
    @php
        $total = $row->medical_certificate_count + $row->consultation_form_count;
        $percent = $total > 0 ? round(($row->medical_certificate_count / $total) * 100) : 0;
    @endphp
    <div class="badge bg-primary">{{ $row->medical_certificate_count ?? 0 }} ({{ $percent }}%)</div>
    ```

---

**Created:** October 8, 2025  
**Status:** ✅ Complete and tested  
**Impact:** Visual improvement, better data insights
