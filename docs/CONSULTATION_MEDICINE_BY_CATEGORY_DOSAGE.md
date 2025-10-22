# Consultation Medicine Selection by Category with Dosage

## Summary

Implemented medicine selection in consultation forms that displays medicines grouped by category with dosage options. When a medicine is selected from a specific dosage, the system deducts the quantity from that specific dosage stock using FIFO (First In, First Out) inventory management.

## Changes Made

### 1. API Endpoint - `getMedicinesByCategory()`

**File:** `app/Http/Controllers/MedicineController.php`

Added new method that returns medicines grouped by category with dosage information:

```php
public function getMedicinesByCategory(): JsonResponse
{
    $categories = \App\Models\Category::with(['medicines' => function ($query) {
        $query->where('available_quantity', '>', 0)
            ->orderBy('name');
    }])->whereHas('medicines', function ($query) {
        $query->where('available_quantity', '>', 0);
    })->orderBy('name')->get();

    $result = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'medicines' => $category->medicines->map(function ($medicine) {
                // Get dosage information for this medicine
                $dosages = PurchasedMedicine::where('medicine_id', $medicine->id)
                    ->where('quantity', '>', 0)
                    ->select('dosage', DB::raw('SUM(quantity) as available_quantity'))
                    ->groupBy('dosage')
                    ->orderBy('dosage')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'dosage' => $item->dosage ?? 'N/A',
                            'available_quantity' => (int) $item->available_quantity
                        ];
                    });

                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'available_quantity' => $medicine->available_quantity,
                    'dosages' => $dosages
                ];
            })
        ];
    });

    return $this->sendResponse($result, 'Medicines retrieved successfully');
}
```

**Response Structure:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Antibiotics",
            "medicines": [
                {
                    "id": 1,
                    "name": "Amoxicillin",
                    "available_quantity": 3000,
                    "dosages": [
                        {
                            "dosage": "250mg",
                            "available_quantity": 1000
                        },
                        {
                            "dosage": "500mg",
                            "available_quantity": 2000
                        }
                    ]
                }
            ]
        }
    ],
    "message": "Medicines retrieved successfully"
}
```

### 2. Routes Added

**Files:**

-   `routes/web.php` (3 locations - admin, receptionist, pharmacist)
-   `routes/staff.php`
-   `routes/doctor.php`

Added route for all user roles:

```php
Route::get('medicines-by-category', [MedicineController::class, 'getMedicinesByCategory'])->name('medicines.by.category');
```

**Route Names by Role:**

-   Admin: `medicines.by.category`
-   Staff: `staff.medicines.by.category`
-   Doctor: `doctors.medicines.by.category`

### 3. Frontend - Consultation Form JavaScript

**File:** `resources/views/requests/forms/consultation_form.blade.php`

Updated medicine fetching to use new API endpoint:

```javascript
async function fetchMedicines() {
    try {
        const response = await fetch('{{ route("medicines.by.category") }}');
        const result = await response.json();

        if (result.success) {
            medicinesData = result.data;
        } else {
            console.error("Error fetching medicines:", result.message);
        }
    } catch (error) {
        console.error("Error fetching medicines:", error);
    }
}
```

Updated medicine dropdown population to use category structure:

```javascript
// Populate medicines grouped by category
medicinesData.forEach((category) => {
    const optgroup = document.createElement("optgroup");
    optgroup.label = category.name;

    category.medicines.forEach((medicine) => {
        const option = document.createElement("option");
        option.value = medicine.id;
        option.textContent = `${medicine.name} (Total: ${medicine.available_quantity})`;
        option.dataset.medicineId = medicine.id;
        option.dataset.medicineName = medicine.name;
        option.dataset.dosages = JSON.stringify(medicine.dosages);
        option.dataset.totalStock = medicine.available_quantity;
        optgroup.appendChild(option);
    });

    medicineSelect.appendChild(optgroup);
});
```

### 4. Backend Deduction Logic (Already Implemented)

**File:** `app/Http/Controllers/RequestDocumentsController.php`

The `handleMedicineDeduction()` method already implements dosage-specific inventory deduction with FIFO:

**Key Features:**

-   ✅ Checks available stock for specific dosage before deduction
-   ✅ Uses FIFO (First In, First Out) - deducts from oldest manufacturing date first
-   ✅ Deducts from specific `PurchasedMedicine` records matching the dosage
-   ✅ Updates medicine's total `available_quantity`
-   ✅ Records medicine usage in `consultation_medicines` table with dosage info
-   ✅ Logs medicine usage activity for audit trail
-   ✅ Clears cache to ensure UI updates immediately
-   ✅ Throws exception if insufficient stock

**FIFO Implementation:**

```php
$purchasedMedicines = \App\Models\PurchasedMedicine::where('medicine_id', $medicineId)
    ->where('dosage', $dosage)
    ->where('quantity', '>', 0)
    ->orderBy('manufacturing_date', 'asc') // FIFO: oldest first
    ->get();

foreach ($purchasedMedicines as $purchasedMedicine) {
    if ($remainingToDeduct <= 0) {
        break;
    }

    if ($purchasedMedicine->quantity >= $remainingToDeduct) {
        // This batch has enough quantity
        $purchasedMedicine->quantity -= $remainingToDeduct;
        $purchasedMedicine->save();
        $remainingToDeduct = 0;
    } else {
        // Use all from this batch and continue
        $remainingToDeduct -= $purchasedMedicine->quantity;
        $purchasedMedicine->quantity = 0;
        $purchasedMedicine->save();
    }
}
```

## Database Structure

### consultation_medicines table

Already has the required columns:

-   `id`
-   `request_document_id`
-   `medicine_id`
-   `dosage` ✅ (stores which dosage variant was used)
-   `quantity`
-   `used_for` (plan/nursing)
-   `dosage_instructions`
-   `created_at`
-   `updated_at`

### purchased_medicines table

Already has the required columns:

-   `id`
-   `medicine_id`
-   `dosage` ✅ (stores strength like "500mg", "200mg")
-   `manufacturing_date`
-   `expiry_date`
-   `quantity`
-   `amount`
-   `tax`

## User Flow

1. **Doctor/Staff opens consultation form**

    - Form loads and fetches medicines grouped by category with dosages

2. **Adds medicine to Plan or Nursing Intervention**

    - Clicks "Add Medicine" button
    - Selects category → Shows medicines in that category
    - Selects medicine → Shows available dosages with quantities
    - Selects dosage → Enables quantity input (max = available quantity for that dosage)
    - Enters quantity and dosage instructions
    - System validates quantity doesn't exceed available stock

3. **Submits consultation form**

    - Backend validates dosage stock availability
    - Deducts from specific `PurchasedMedicine` records using FIFO
    - Updates medicine's total available quantity
    - Records medicine usage with dosage in `consultation_medicines`
    - Logs activity for audit trail

4. **Inventory Management**
    - FIFO ensures oldest stock (by manufacturing date) is used first
    - Each dosage variant is tracked separately
    - Stock warnings shown when quantity is low or out of stock

## Features

### Frontend Features

-   ✅ Medicine selection grouped by category
-   ✅ Dosage dropdown showing available quantities per dosage
-   ✅ Stock validation (max quantity = available for selected dosage)
-   ✅ Low stock warnings (< 10 units)
-   ✅ Out of stock warnings (0 units)
-   ✅ Quantity validation to prevent over-ordering
-   ✅ Separate medicine lists for "Plan" and "Nursing Intervention"

### Backend Features

-   ✅ API endpoint returns only medicines with available stock
-   ✅ Dosages grouped and summed from purchased_medicines
-   ✅ FIFO inventory deduction (oldest first)
-   ✅ Dosage-specific stock checking
-   ✅ Transaction safety with exception handling
-   ✅ Activity logging for audit trail
-   ✅ Cache clearing for immediate UI updates

## Testing Checklist

-   [ ] Verify API endpoint returns correct structure
-   [ ] Test medicine selection shows categories correctly
-   [ ] Test dosage dropdown shows correct quantities
-   [ ] Test quantity validation prevents exceeding stock
-   [ ] Test stock warnings display correctly
-   [ ] Test form submission deducts from correct dosage
-   [ ] Test FIFO deduction uses oldest manufacturing date first
-   [ ] Test total medicine quantity updates correctly
-   [ ] Test consultation_medicines records have correct dosage
-   [ ] Test activity log captures medicine usage
-   [ ] Test insufficient stock throws exception
-   [ ] Test for all user roles (Admin, Staff, Doctor)

## Files Modified

1. **app/Http/Controllers/MedicineController.php**

    - Added `getMedicinesByCategory()` method

2. **routes/web.php**

    - Added `medicines-by-category` route (3 sections)

3. **routes/staff.php**

    - Added `medicines-by-category` route

4. **routes/doctor.php**

    - Added `medicines-by-category` route

5. **resources/views/requests/forms/consultation_form.blade.php**
    - Updated `fetchMedicines()` to use new API endpoint
    - Updated medicine dropdown population logic

## No Changes Required

-   ✅ `ConsultationMedicine` model - already has dosage field
-   ✅ `RequestDocumentsController::handleMedicineDeduction()` - already implements dosage-specific deduction with FIFO
-   ✅ Database schema - consultation_medicines and purchased_medicines tables already have dosage columns

## Notes

-   The implementation leverages existing database structure (dosage columns already existed)
-   Backend deduction logic was already implemented with FIFO and dosage support
-   Only needed to add API endpoint and update frontend to use it
-   System maintains complete audit trail of medicine usage
-   FIFO ensures proper inventory rotation (oldest stock used first)
