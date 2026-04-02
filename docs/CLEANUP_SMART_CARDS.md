# CLEANUP: Patient Smart Cards / QR Codes

**Phase:** 4 (independent — but RENAME skeleton view BEFORE deleting smart card views)  
**Status:** ⬜ Not started

---

## SCOPE

Remove the patient smart card / QR code feature. This is a **views-only / abandoned feature** — no PHP backend class (`GeneratePatientSmartCardsTable`) exists. Routes referenced in views were never defined in any route file.

---

## ⚠️ CRITICAL FIRST STEP — RENAME SKELETON VIEW

The view `resources/views/livewire/smart_patient_cards_skeleton.blade.php` is **re-used as a generic loading placeholder** by 5 active Livewire tables:

| Component                  | Uses Skeleton                                       | Notes                               |
| -------------------------- | --------------------------------------------------- | ----------------------------------- |
| `MedicineBrandTable.php`   | `@include('livewire.smart_patient_cards_skeleton')` | KEEP — update include               |
| `MedicineBillTable.php`    | `@include('livewire.smart_patient_cards_skeleton')` | KEEP — update include               |
| `MedicineGenericTable.php` | `@include('livewire.smart_patient_cards_skeleton')` | KEEP — update include               |
| `ServiceCategoryTable.php` | `@include('livewire.smart_patient_cards_skeleton')` | **Being DELETED in Phase 2** — skip |
| `SpecializationTable.php`  | `@include('livewire.smart_patient_cards_skeleton')` | KEEP — update include               |

> ℹ️ `ServiceCategoryTable.php` will be deleted in Phase 2, so only **4 tables** need their `@include` updated (not 5).

**BEFORE deleting smart card views:**

1. **Rename** `smart_patient_cards_skeleton.blade.php` → `loading_skeleton.blade.php`

    ```
    mv resources/views/livewire/smart_patient_cards_skeleton.blade.php
       resources/views/livewire/loading_skeleton.blade.php
    ```

2. **Update all 5 `@include` calls** in the Livewire PHP components above:  
   Change: `@include('livewire.smart_patient_cards_skeleton')`  
   To: `@include('livewire.loading_skeleton')`

3. **Then proceed** with deleting smart card views.

---

## VIEW DIRECTORIES TO DELETE

| Path                                            | Status                                             |
| ----------------------------------------------- | -------------------------------------------------- |
| `resources/views/generate_patient_smart_cards/` | ⬜ Delete entire directory (after skeleton rename) |
| `resources/views/smart_card_pdf/`               | ⬜ Delete entire directory                         |

### Files inside `generate_patient_smart_cards/`:

- `index.blade.php`
- `components/show_card.blade.php`
- `components/modal.blade.php`
- `components/add_button.blade.php`
- `components/action.blade.php`

---

## INCLUDES TO REMOVE (in kept views)

| File                                                    | Line | Include to Remove                                               | Status    |
| ------------------------------------------------------- | ---- | --------------------------------------------------------------- | --------- |
| `resources/views/patient_dashboard/index.blade.php`     | 457  | `@include('generate_patient_smart_cards/components/show_card')` | ⬜ Remove |
| `resources/views/patients/appointments/index.blade.php` | 14   | `@include('generate_patient_smart_cards/components/show_card')` | ⬜ Remove |

---

## JAVASCRIPT / CSS TO REMOVE

### `webpack.mix.js`

- Remove the smart card CSS entry (if a dedicated `smart-card.css` or similar entry exists)
- Remove any Pusher/Echo imports if still present (also covered in CLEANUP_UNUSED.md)

### `resources/assets/js/` (if smart card JS files exist)

- Check for and delete: `smart_card.js`, `patient_smart_card.js` or similar

---

## COMPOSER PACKAGE TO REMOVE

```bash
composer remove simplesoftwareio/simple-qrcode
```

> ⚠️ Run AFTER removing all `QrCode::` references from PHP files and views.

---

## CONFIG TO UPDATE

### `config/app.php`

Remove from `aliases` array:

```php
'QrCode' => SimpleSoftware\QrCode\Facades\QrCode::class,
```

Remove from `providers` array (if manually registered):

```php
SimpleSoftware\QrCode\QrCodeServiceProvider::class,
```

---

## LANGUAGE KEYS TO REMOVE (optional cleanup)

In `lang/en/messages.php` (or equivalent), remove keys under `smart_patient_card`:

```php
'smart_patient_card' => [
    'smart_card' => '...',
    'generate_patient_smart_cards' => '...',
    // ... all nested keys
],
```

---

## HEADER CLEANUP (already commented — just remove block)

### `resources/views/layouts/header.blade.php` (lines 24–33)

The smart card button is already wrapped in `{{-- ... --}}` Blade comments.  
Action: Delete the entire commented block cleanly.

```blade
{{--
<a href="javascript:void(0)" class="btn px-5 text-primary fs-3 show_patient_card"
   data-id="{{getLogInUser()->patient->id}}"
   ...>
    <i class="fa-solid fa-id-card" ...></i>
</a>
--}}
```

---

## SEEDER CLEANUP

No dedicated permission for smart cards — no seeder changes needed.

---

## COMPLETION CHECKLIST

- [ ] **Step 1 — RENAME** `smart_patient_cards_skeleton.blade.php` → `loading_skeleton.blade.php`
- [ ] **Step 2 — UPDATE** 5 Livewire component `@include` references to use `loading_skeleton`
- [ ] Delete view directories: `generate_patient_smart_cards/`, `smart_card_pdf/`
- [ ] Remove `@include('generate_patient_smart_cards/components/show_card')` from `patient_dashboard/index.blade.php` (line 457)
- [ ] Remove `@include('generate_patient_smart_cards/components/show_card')` from `patients/appointments/index.blade.php` (line 14)
- [ ] Remove commented smart card button block from `header.blade.php` (lines 24–33)
- [ ] Remove QrCode facade from `config/app.php`
- [ ] Run `composer remove simplesoftwareio/simple-qrcode`
- [ ] Remove smart card CSS/JS entries from `webpack.mix.js` (if any)
- [ ] Remove language keys for `smart_patient_card` from lang files

---

_Phase 4 — Independent. Rename skeleton view FIRST before any deletions._
