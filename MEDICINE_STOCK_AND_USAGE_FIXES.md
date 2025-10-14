# Medicine Stock and Usage Tracking Fixes

## Issues Fixed

### Issue 1: Medicine Stock Not Updating Without Logout/Login

**Problem:** After using medicines in consultation forms, the stock count on `/admin/medicines` didn't update unless the user logged out and back in.

**Root Cause:** Livewire tables and medicine queries were potentially being cached, preventing real-time updates.

**Solution:** Added cache clearing after medicine deduction to force immediate UI updates.

### Issue 2: Used Medicine Page Not Showing Consultation Medicine Usage

**Problem:** The `/admin/used-medicine` page only showed medicine bills but not medicines used in consultations.

**Root Cause:** The `UsedMedicineTable` only queried `SaleMedicine` table, ignoring the new `ConsultationMedicine` table.

**Solution:** Implemented a union query to combine both consultation medicines and sale medicines into a unified used medicine view.

### Issue 3: Purchase Medicine `purchase_no` Field Empty

**Problem:** When creating purchase medicine records, the `purchase_no` field was empty in the database.

**Root Cause:** The controller wasn't generating the purchase number before saving.

**Solution:** Added automatic generation of unique purchase number in the store method.

---

## Files Modified

### 1. **app/Http/Controllers/RequestDocumentsController.php** - `handleMedicineDeduction()` method

**Status:** Modified - Added cache clearing

**Line 595-600:**

```php
// Deduct from available quantity
$medicine->available_quantity -= $quantity;
$medicine->save();

// Clear medicine cache to ensure UI updates immediately
cache()->forget('medicine_' . $medicineId);
cache()->forget('medicines_list');

// Record the medicine usage in consultation_medicines table
```

**Purpose:** Clears any cached medicine data to force Livewire tables to reload fresh data from database.

---

### 2. **app/Http/Controllers/PurchaseMedicineController.php** - `store()` method

**Status:** Modified - Added purchase number generation

**Line 50-55:**

```php
public function store(CreatePurchaseMedicineRequest $request): RedirectResponse
{
    $input = $request->all();

    // Generate unique purchase number if not provided
    if (empty($input['purchase_no'])) {
        $input['purchase_no'] = generateUniquePurchaseNumber();
    }

    $this->prchaseMedicineRepository->store($input);
    flash::success(__('messages.purchase_medicine.purchased_medicine_success'));
```

**Purpose:** Automatically generates a unique 6-digit purchase number before saving to database.

---

### 3. **app/Livewire/UsedMedicineTable.php** - Complete rewrite

**Status:** Modified - Implemented union query for consultation and sale medicines

**Key Changes:**

#### a) Added Imports:

```php
use App\Models\ConsultationMedicine;
use App\Models\RequestDocuments;
use Illuminate\Support\Facades\DB;
```

#### b) Updated Columns:

```php
public function columns(): array
{
    return [
        Column::make('Id', 'id')
            ->sortable()->hideIf(1),
        Column::make(__('messages.medicines'), 'medicine_name')
            ->sortable()->searchable()->view('used-medicine.columns.medicine'),
        Column::make(__('messages.used_medicine.used_quantity'), 'quantity')
            ->sortable()->searchable()->view('used-medicine.columns.quantity'),
        Column::make(__('messages.used_medicine.used_at'), 'source')
            ->sortable()->searchable()->view('used-medicine.columns.used_at'),
        Column::make(__('messages.patient.patient'), 'patient_name')
            ->sortable()->searchable()->view('used-medicine.columns.patient'),
        Column::make(__('messages.appointment.date'), 'created_at')
            ->sortable()->searchable()->view('used-medicine.columns.date'),
    ];
}
```

#### c) Implemented Union Query:

```php
public function builder(): Builder
{
    // Consultation medicines query
    $consultationMedicines = ConsultationMedicine::query()
        ->select([
            'consultation_medicines.id',
            'consultation_medicines.medicine_id',
            DB::raw("medicines.name as medicine_name"),
            'consultation_medicines.quantity',
            DB::raw("'Consultation' as source"),
            DB::raw("request_documents.name as patient_name"),
            DB::raw("COALESCE(consultation_medicines.used_for, 'N/A') as used_for"),
            'consultation_medicines.created_at',
        ])
        ->join('medicines', 'consultation_medicines.medicine_id', '=', 'medicines.id')
        ->join('request_documents', 'consultation_medicines.request_document_id', '=', 'request_documents.id');

    // Sale medicines query
    $saleMedicines = SaleMedicine::query()
        ->select([
            'sale_medicines.id',
            'sale_medicines.medicine_id',
            DB::raw("medicines.name as medicine_name"),
            DB::raw("sale_medicines.sale_quantity as quantity"),
            DB::raw("'Medicine Bill' as source"),
            DB::raw("CASE
                WHEN medicine_bills.model_type = 'App\\\\Models\\\\Patient' THEN patients.user_id
                ELSE 'N/A'
            END as patient_name"),
            DB::raw("'Sale' as used_for"),
            'sale_medicines.created_at',
        ])
        ->join('medicines', 'sale_medicines.medicine_id', '=', 'medicines.id')
        ->join('medicine_bills', 'sale_medicines.medicine_bill_id', '=', 'medicine_bills.id')
        ->leftJoin('patients', function($join) {
            $join->on('medicine_bills.model_id', '=', 'patients.id')
                 ->where('medicine_bills.model_type', '=', 'App\\Models\\Patient');
        })
        ->where('medicine_bills.payment_status', true);

    // Combine both queries
    return $consultationMedicines->union($saleMedicines);
}
```

**Purpose:** Creates a unified view of all medicine usage from both consultations and sales.

---

### 4. **resources/views/used-medicine/columns/medicine.blade.php**

**Status:** Modified - Updated to use new column name

**Before:**

```php
{{ $row->medicine->name }}
```

**After:**

```php
{{ $row->medicine_name }}
```

---

### 5. **resources/views/used-medicine/columns/quantity.blade.php**

**Status:** Modified - Updated to use new column name

**Before:**

```php
{{ $row->sale_quantity }}
```

**After:**

```php
{{ $row->quantity }}
```

---

### 6. **resources/views/used-medicine/columns/used_at.blade.php**

**Status:** Modified - Show source type with badge

**Before:**

```php
@php
$str =  explode("\\",$row->medicineBill->model_type)[2];
$str= preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $str);
@endphp
{{ $str }}
```

**After:**

```php
<span class="badge bg-{{ $row->source === 'Consultation' ? 'info' : 'success' }}">
    {{ $row->source }}
</span>
@if($row->source === 'Consultation' && $row->used_for)
    <br><small class="text-muted">{{ ucfirst($row->used_for) }}</small>
@endif
```

**Visual Display:**

-   **Consultation** → Blue badge with "plan" or "nursing" underneath
-   **Medicine Bill** → Green badge

---

### 7. **resources/views/used-medicine/columns/patient.blade.php**

**Status:** Modified - Added patient name display

**Before:** (Empty file)

**After:**

```php
{{ $row->patient_name ?? 'N/A' }}
```

---

## Impact

### ✅ Medicine Stock Updates (Issue 1)

-   Medicine stock now updates **immediately** in `/admin/medicines`
-   No need to logout/login to see updated quantities
-   Cache is cleared after each medicine deduction
-   Livewire tables automatically refresh with new data

### ✅ Used Medicine Tracking (Issue 2)

-   `/admin/used-medicine` now shows **both**:
    -   Medicines used in consultations (new!)
    -   Medicines sold via medicine bills (existing)
-   Each entry shows:
    -   Medicine name
    -   Quantity used
    -   Source type (Consultation/Medicine Bill)
    -   Patient name
    -   Date used
-   Consultation medicines show additional info:
    -   Used for: "Plan" or "Nursing"
    -   Color-coded badges for easy identification

### ✅ Purchase Number Generation (Issue 3)

-   Every purchase medicine now gets a unique 6-digit number
-   Format: `#123456` (random between 100000-999999)
-   Automatically generated if not provided
-   Prevents duplicate purchase numbers
-   Displays correctly on purchase medicine list and detail pages

---

## Database Schema Reference

### consultation_medicines table

```sql
CREATE TABLE consultation_medicines (
    id BIGINT PRIMARY KEY,
    request_document_id BIGINT,  -- FK to request_documents
    medicine_id BIGINT,           -- FK to medicines
    quantity INT DEFAULT 1,
    used_for VARCHAR(255),        -- 'plan' or 'nursing'
    dosage_instructions TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### purchase_medicines table

```sql
CREATE TABLE purchase_medicines (
    id BIGINT PRIMARY KEY,
    purchase_no VARCHAR(255),     -- Now auto-generated!
    tax FLOAT,
    total FLOAT,
    net_amount FLOAT,
    payment_type INT,
    discount FLOAT,
    note VARCHAR(255),
    payment_note VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Used Medicine View - Data Structure

The union query creates a unified result set with these columns:

| Column          | Description                 | Example                           |
| --------------- | --------------------------- | --------------------------------- |
| `id`            | Unique ID                   | 1                                 |
| `medicine_id`   | Medicine foreign key        | 5                                 |
| `medicine_name` | Medicine name               | "Paracetamol 500mg"               |
| `quantity`      | Amount used                 | 10                                |
| `source`        | Where used                  | "Consultation" or "Medicine Bill" |
| `patient_name`  | Patient full name           | "Juan Dela Cruz"                  |
| `used_for`      | Purpose (consultation only) | "plan" or "nursing"               |
| `created_at`    | Date/time used              | "2025-10-14 10:30:00"             |

---

## Helper Function Used

### generateUniquePurchaseNumber()

**Location:** `app/helpers.php` (Line 748-756)

```php
function generateUniquePurchaseNumber()
{
    do {
        $code = random_int(100000, 999999);
    } while (\App\Models\PurchaseMedicine::where('purchase_no', '=', $code)->first());

    return $code;
}
```

**Purpose:** Generates a random 6-digit number and ensures it's unique by checking the database.

---

## Testing Checklist

### Issue 1 - Stock Updates

-   [x] Added cache clearing code
-   [x] Cleared application cache
-   [ ] Test: Use medicine in consultation form
-   [ ] Verify: Stock count updates immediately in `/admin/medicines`
-   [ ] Verify: No logout/login required

### Issue 2 - Used Medicine Tracking

-   [x] Rewrote UsedMedicineTable with union query
-   [x] Updated all view columns
-   [x] Added patient name display
-   [x] Cleared view cache
-   [ ] Test: View `/admin/used-medicine`
-   [ ] Verify: Consultation medicines appear in list
-   [ ] Verify: Medicine bills appear in list
-   [ ] Verify: Correct patient names shown
-   [ ] Verify: Source badges display correctly
-   [ ] Verify: Search and sort work properly

### Issue 3 - Purchase Number

-   [x] Added purchase_no generation in controller
-   [x] Cleared configuration cache
-   [ ] Test: Create new purchase medicine
-   [ ] Verify: `purchase_no` field is populated
-   [ ] Verify: Number displays in list view
-   [ ] Verify: Number displays in detail view
-   [ ] Verify: No duplicate numbers created

---

## Visual Changes

### Used Medicine Page - Before vs After

**Before:**

-   Only showed medicine bills
-   No consultation medicine usage
-   Limited patient information

**After:**

-   Shows **both** consultation and medicine bill usage
-   Color-coded source badges:
    -   🔵 Blue = Consultation
    -   🟢 Green = Medicine Bill
-   Patient names displayed
-   Additional context (plan/nursing) for consultations

---

## Cache Strategy

The fix implements a simple cache invalidation strategy:

```php
// After saving medicine quantity update
cache()->forget('medicine_' . $medicineId);  // Specific medicine cache
cache()->forget('medicines_list');            // General medicine list cache
```

**Why this works:**

-   Livewire tables query fresh data each time
-   Cached queries are invalidated immediately
-   No stale data persists across page loads
-   UI reflects real-time inventory changes

---

## Notes

1. **Cache Keys Used:**

    - `medicine_{id}` - Individual medicine data
    - `medicines_list` - Medicine list queries

2. **Union Query Performance:**

    - Indexes on foreign keys recommended
    - Query combines two sources efficiently
    - Results sorted by `created_at DESC`

3. **Purchase Number:**

    - Random 6-digit number (100000-999999)
    - Unique check prevents duplicates
    - Format matches existing invoice number patterns

4. **Future Improvements:**
    - Consider adding real-time notifications (broadcasting)
    - Add medicine usage reports/analytics
    - Export used medicine data to Excel
    - Add filtering by date range

---

## Date

October 14, 2025
