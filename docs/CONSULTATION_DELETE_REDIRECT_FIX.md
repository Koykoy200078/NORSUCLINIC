# Consultation History Delete Redirect Fix

**Date:** October 16, 2025  
**Issue:** When deleting consultation history from patient view page, the system was redirecting to request-documents route instead of back to patient history page

---

## 🐛 Problem Description

When a user (clinic_admin, staff, or doctor) deleted a consultation record from the patient history page (`/patients/{id}/history`), the system was redirecting to:

-   `http://127.0.0.1:8000/staff/request-documents/2` ❌

Instead of redirecting to:

-   `http://127.0.0.1:8000/staff/patients/2/history` ✅

---

## 🔧 Solution Implemented

### File Modified

**File:** `resources/views/patients/view_patient.blade.php`  
**Section:** Consultation History table - Delete button forms

### Changes Made

Added a hidden input field `redirect_patient_id` to each delete form to tell the controller where to redirect after deletion:

```php
<form action="{{ route('staff.request-documents.destroy', $consultation->id) }}" method="POST" class="d-inline delete-form">
    @csrf
    @method('DELETE')
    <input type="hidden" name="redirect_patient_id" value="{{ $patient->id }}">  <!-- NEW -->
    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this consultation record?')">
        <i class="fas fa-trash"></i>
    </button>
</form>
```

### Controller Logic

The `RequestDocumentsController::destroy()` method already had logic to check for `redirect_patient_id`:

```php
public function destroy(RequestDocuments $request_document)
{
    // Delete the document
    $request_document->delete();

    // Check if we have a redirect_patient_id (from patient history page)
    if (request()->has('redirect_patient_id')) {
        $patientId = request()->input('redirect_patient_id');

        // Redirect back to patient history based on role
        if (isRole('clinic_admin')) {
            return redirect()->route('patients.showMyHistory', ['patient' => $patientId])
                ->with('success', 'Medical certificate deleted successfully.');
        } elseif (isRole('staff')) {
            return redirect()->route('staff.patients.showMyHistory', ['patient' => $patientId])
                ->with('success', 'Medical certificate deleted successfully.');
        } elseif (isRole('doctor')) {
            return redirect()->route('doctors.patients.showMyHistory', ['patient' => $patientId])
                ->with('success', 'Medical certificate deleted successfully.');
        }
    }

    // Default: Redirect to request documents index
    // ...
}
```

---

## ✅ Result

### Role-Based Redirects After Delete

| User Role        | Delete From                   | Redirects To                     |
| ---------------- | ----------------------------- | -------------------------------- |
| **clinic_admin** | `/patients/2/history`         | ✅ `/patients/2/history`         |
| **staff**        | `/staff/patients/2/history`   | ✅ `/staff/patients/2/history`   |
| **doctor**       | `/doctors/patients/2/history` | ✅ `/doctors/patients/2/history` |

### What Changed

**Before:**

1. User clicks delete button on patient history page
2. Consultation record is deleted
3. User is redirected to `/staff/request-documents/{id}` ❌
4. User sees 404 or wrong page

**After:**

1. User clicks delete button on patient history page
2. Consultation record is deleted
3. `redirect_patient_id` is passed to controller
4. User is redirected back to `/staff/patients/{id}/history` ✅
5. User sees success message on patient history page

---

## 🎯 Applied To

This fix was applied to **all three user roles** in the consultation history delete forms:

1. ✅ **clinic_admin** - Form includes `redirect_patient_id`
2. ✅ **staff** - Form includes `redirect_patient_id`
3. ✅ **doctor** - Form includes `redirect_patient_id`

---

## 📝 Notes

-   The same pattern is already used in the Medical Certificate delete functionality (via JavaScript)
-   No changes were needed to the controller - it already handled the redirect logic
-   The fix ensures users stay on the patient history page after deleting consultation records
-   Success message is displayed on the patient history page

---

## ✨ Testing Checklist

-   [ ] Test as **clinic_admin** - delete consultation from patient history
-   [ ] Test as **staff** - delete consultation from patient history
-   [ ] Test as **doctor** - delete consultation from patient history
-   [ ] Verify redirect goes to correct role-based route
-   [ ] Verify success message is displayed
-   [ ] Verify consultation record is actually deleted from database

---

_Fix completed: October 16, 2025_
