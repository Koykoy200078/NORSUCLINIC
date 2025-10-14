# Medicine Inventory Integration for Consultation Form

## Overview

This feature allows nurses and doctors to add medicines to the **Plan** and **Nursing Intervention** fields in consultation forms. When medicines are added, they are automatically deducted from the inventory at `/admin/medicines`.

## Features Implemented

### 1. Database Structure

-   **New Table**: `consultation_medicines`
    -   Tracks which medicines were used in each consultation
    -   Records quantity used, dosage instructions, and where it was used (Plan or Nursing Intervention)
    -   Automatically deducts from medicine inventory

### 2. User Interface

-   **Medicine Selection Buttons**: Green "+ Add Medicine" buttons in both:
    -   Plan section
    -   Nursing Intervention section
-   **Medicine Row Fields**:

    -   Medicine dropdown (shows name and available stock)
    -   Quantity input (validates against available stock)
    -   Dosage instructions input (e.g., "1 tablet 3x a day")
    -   Remove button (red trash icon)

-   **Stock Validation**:
    -   Shows warning if medicine stock is low (< 10 units)
    -   Prevents selection if medicine is out of stock
    -   Prevents entering quantity higher than available stock

### 3. Backend Processing

-   **Automatic Inventory Deduction**:

    -   When consultation form is submitted, medicines are deducted from `medicines.available_quantity`
    -   Creates record in `consultation_medicines` table
    -   Logs all medicine usage for audit trail

-   **Stock Validation**:
    -   Checks if enough stock available before deduction
    -   Throws error if insufficient stock
    -   Prevents negative inventory

## Files Created/Modified

### New Files:

1. **Migration**: `database/migrations/2025_10_13_000001_create_consultation_medicines_table.php`

    - Creates `consultation_medicines` table

2. **Model**: `app/Models/ConsultationMedicine.php`
    - Handles consultation medicine records
    - Relationships with RequestDocuments and Medicine

### Modified Files:

1. **`resources/views/requests/forms/consultation_form.blade.php`**

    - Added medicine selection UI for Plan field
    - Added medicine selection UI for Nursing Intervention field
    - Added CSS for medicine rows
    - Added JavaScript for dynamic medicine selection

2. **`routes/api.php`**

    - Added `/api/medicines` endpoint to fetch available medicines

3. **`app/Http/Controllers/RequestDocumentsController.php`**

    - Added `handleMedicineDeduction()` method
    - Modified `storeConsultationForm()` to call medicine deduction

4. **`app/Models/RequestDocuments.php`**
    - Added `consultationMedicines()` relationship

## How to Use

### For Nurses/Doctors:

1. **Fill out consultation form** as usual
2. **In Plan section**:

    - Click "+ Add Medicine" button
    - Select medicine from dropdown (shows available stock)
    - Enter quantity needed
    - Enter dosage instructions (optional)
    - Can add multiple medicines

3. **In Nursing Intervention section**:

    - Same process as Plan section
    - Can add different medicines or same ones

4. **Submit form**:
    - Medicines automatically deducted from inventory
    - System validates stock availability
    - Shows error if insufficient stock

### Stock Warnings:

-   **Out of Stock**: Medicine cannot be selected
-   **Low Stock** (< 10 units): Warning message shown
-   **Exceeds Available**: Quantity automatically adjusted to max available

## Database Schema

### `consultation_medicines` Table:

```sql
CREATE TABLE consultation_medicines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    request_document_id BIGINT NOT NULL,
    medicine_id BIGINT NOT NULL,
    quantity INT DEFAULT 1,
    used_for VARCHAR(255) NULL,  -- 'plan' or 'nursing'
    dosage_instructions TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (request_document_id) REFERENCES request_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);
```

## API Endpoint

### GET `/api/medicines`

Returns list of available medicines with stock information.

**Response**:

```json
[
    {
        "id": 1,
        "name": "Paracetamol 500mg",
        "available_quantity": 150,
        "salt_composition": "Paracetamol"
    },
    {
        "id": 2,
        "name": "Amoxicillin 500mg",
        "available_quantity": 75,
        "salt_composition": "Amoxicillin"
    }
]
```

## Inventory Deduction Logic

```php
// When consultation form is submitted:
1. Validate medicine availability
2. For each medicine selected:
   - Check if available_quantity >= requested quantity
   - If yes:
     - Deduct from available_quantity
     - Save medicine record
     - Create consultation_medicines entry
     - Log the transaction
   - If no:
     - Throw error
     - Prevent form submission
```

## Example Usage Scenario

**Scenario**: Patient with headache and fever

**Plan Field**:

-   Medicine 1: Paracetamol 500mg, Quantity: 10, Dosage: "1 tablet every 6 hours"
-   Medicine 2: Ibuprofen 400mg, Quantity: 5, Dosage: "1 tablet every 8 hours"

**Nursing Intervention Field**:

-   Medicine 1: Vitamin C 500mg, Quantity: 7, Dosage: "1 tablet daily for 1 week"

**Result After Submission**:

-   Paracetamol: Stock reduced by 10
-   Ibuprofen: Stock reduced by 5
-   Vitamin C: Stock reduced by 7
-   3 records created in `consultation_medicines` table
-   All logged in system logs

## Future Enhancements (Optional)

1. **Medicine Usage History**:

    - View all consultations that used a specific medicine
    - Generate medicine usage reports

2. **Low Stock Alerts**:

    - Email notification when medicine stock is low
    - Dashboard widget showing low-stock medicines

3. **Medicine Expiry Tracking**:

    - Show expiry date in medicine dropdown
    - Prevent selection of expired medicines

4. **Batch/Lot Tracking**:

    - Track which batch/lot was used
    - FIFO (First In First Out) deduction

5. **Edit Consultation**:
    - Allow modifying medicines in existing consultations
    - Restore stock when medicines are removed

## Testing Checklist

-   [ ] Can add medicine to Plan field
-   [ ] Can add medicine to Nursing Intervention field
-   [ ] Can add multiple medicines
-   [ ] Can remove medicine before submission
-   [ ] Stock validation works (prevents over-ordering)
-   [ ] Form submission deducts from inventory
-   [ ] Low stock warning appears correctly
-   [ ] Out of stock prevents selection
-   [ ] Dosage instructions saved correctly
-   [ ] consultation_medicines records created
-   [ ] Logs created for audit trail

## Troubleshooting

### Issue: "Medicines not showing in dropdown"

**Solution**: Check that:

1. Medicines exist in database
2. Medicines have `available_quantity > 0`
3. API endpoint `/api/medicines` is accessible
4. User is authenticated

### Issue: "Stock not deducting"

**Solution**: Check:

1. `handleMedicineDeduction()` method is called
2. No exceptions thrown during submission
3. Check Laravel logs for errors

### Issue: "Can select more than available stock"

**Solution**:

1. JavaScript validation should prevent this
2. Backend validation throws exception
3. Check browser console for JS errors

## Support

For issues or questions, check:

-   Laravel logs: `storage/logs/laravel.log`
-   Browser console for JavaScript errors
-   Database records in `consultation_medicines` table
