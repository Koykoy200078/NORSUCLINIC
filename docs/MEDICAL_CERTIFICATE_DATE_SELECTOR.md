# Medical Certificate - Multiple Date Selector Feature

**Date:** October 16, 2025  
**Feature:** Enhanced date selection for "examined_on" field with support for single dates, date ranges, and multiple dates

---

## 🎯 Feature Overview

The medical certificate form now supports three types of date input:

1. **Single Date** - Traditional single date selection (e.g., `10/16/2025`)
2. **Date Range** - Start and end date range (e.g., `10/12/2025 - 10/16/2025`)
3. **Multiple Dates** - Multiple non-consecutive dates (e.g., `10/12/2025, 10/14/2025, 10/16/2025`)

---

## 📋 Implementation Details

### Files Modified

1. **View File:** `resources/views/requests/forms/medical_certificate.blade.php`
2. **Controller:** `app/Http/Controllers/RequestDocumentsController.php`

---

## 🖥️ User Interface Changes

### Before

```html
<input
    type="date"
    name="examined_on"
    value="{{ date('Y-m-d') }}"
    max="{{ date('Y-m-d') }}"
/>
```

### After

```html
<input
    type="text"
    id="examined_on_display"
    name="examined_on"
    placeholder="Click to select date(s)"
    readonly
    required
/>
<input type="hidden" id="examined_on_value" name="examined_on_value" />
<button type="button" id="open_date_selector" class="btn btn-sm btn-primary">
    <i class="fas fa-calendar-alt"></i> Select Dates
</button>
```

---

## 🎨 Modal Interface

### Date Type Selection

Users can choose between:

-   **Single Date** - Select one examination date
-   **Date Range** - Select start and end dates
-   **Multiple Dates** - Add multiple individual dates

### Single Date Section

-   Simple date picker
-   Maximum date: Today's date
-   Default: Today's date

### Date Range Section

-   **Start Date** picker
-   **End Date** picker (minimum = start date)
-   Validation: End date cannot be before start date

### Multiple Dates Section

-   Date input with "Add" button
-   Visual list of selected dates
-   Remove individual dates with × button
-   Dates automatically sorted chronologically
-   Duplicate prevention

### Preview Section

-   Real-time preview of selected dates
-   Shows formatted dates as they will appear
-   Updates when dates are changed

---

## 💾 Data Storage Format

### Display Format (shown to user)

-   **Single:** `10/16/2025`
-   **Range:** `10/12/2025 - 10/16/2025`
-   **Multiple:** `10/12/2025, 10/14/2025, 10/16/2025`

### Storage Format (saved to database)

-   **Single:** `2025-10-16`
-   **Range:** `2025-10-12|2025-10-16|range`
-   **Multiple:** `2025-10-12,2025-10-14,2025-10-16|multiple`

---

## 🔧 Technical Implementation

### JavaScript Functionality

```javascript
// Date format conversion
function formatDateDisplay(dateString) {
    const date = new Date(dateString + "T00:00:00");
    return date.toLocaleDateString("en-US", {
        month: "2-digit",
        day: "2-digit",
        year: "numeric",
    });
}

// Storage format examples:
// Single: "2025-10-16"
// Range: "2025-10-12|2025-10-16|range"
// Multiple: "2025-10-12,2025-10-14,2025-10-16|multiple"
```

### Controller Processing

```php
// In RequestDocumentsController::storeMedicalCertificate()
$examinedOn = $data['examined_on'];
if (isset($data['examined_on_value']) && !empty($data['examined_on_value'])) {
    // Use the hidden value which contains the raw date data
    $examinedOn = $data['examined_on_value'];
}
$data['examined_on'] = $examinedOn;
```

---

## 📝 Form Fields

### Visible Field

-   **Field:** `examined_on_display`
-   **Type:** Text (readonly)
-   **Purpose:** Display formatted dates to user
-   **Example:** `10/12/2025 - 10/16/2025`

### Hidden Field

-   **Field:** `examined_on_value`
-   **Type:** Hidden
-   **Purpose:** Store raw date data for processing
-   **Example:** `2025-10-12|2025-10-16|range`

---

## ✅ Features & Validation

### Date Restrictions

✅ Maximum date is today's date (no future dates)  
✅ End date in range cannot be before start date  
✅ Duplicate dates prevented in multiple dates mode  
✅ All fields are required before applying

### User Experience

✅ Real-time preview of selected dates  
✅ Visual feedback with styled date badges  
✅ Easy removal of individual dates  
✅ Automatic date sorting (chronological order)  
✅ Bootstrap modal interface  
✅ Responsive design

### Data Integrity

✅ Proper date format conversion (Y-m-d for database)  
✅ Timezone-safe date handling  
✅ Clear separation between display and storage formats  
✅ Backward compatible with single date inputs

---

## 🎯 Usage Examples

### Example 1: Single Date

**User Action:** Select single date → 10/16/2025  
**Display:** `10/16/2025`  
**Storage:** `2025-10-16`

### Example 2: Date Range

**User Action:** Start: 10/12/2025, End: 10/16/2025  
**Display:** `10/12/2025 - 10/16/2025`  
**Storage:** `2025-10-12|2025-10-16|range`

### Example 3: Multiple Dates

**User Action:** Add dates: 10/12, 10/14, 10/16  
**Display:** `10/12/2025, 10/14/2025, 10/16/2025`  
**Storage:** `2025-10-12,2025-10-14,2025-10-16|multiple`

---

## 🔍 How to Use (User Guide)

1. **Open Medical Certificate Form**

    - Navigate to Create Medical Certificate page
    - Fill in patient information

2. **Select Examination Date(s)**

    - Click the "Select Dates" button (📅 icon)
    - Modal window will open

3. **Choose Date Type**

    - Click one of three options:
        - Single Date
        - Date Range
        - Multiple Dates

4. **Enter Dates**

    - **Single:** Pick one date from calendar
    - **Range:** Pick start and end dates
    - **Multiple:** Pick date and click "Add" (repeat as needed)

5. **Preview**

    - Check the preview section to see formatted dates

6. **Apply**
    - Click "Apply Dates" button
    - Dates will appear in the form
    - Continue filling out the rest of the certificate

---

## 🧪 Testing Checklist

### Single Date Mode

-   [ ] Default date is today
-   [ ] Cannot select future dates
-   [ ] Date displays in MM/DD/YYYY format
-   [ ] Form submission works correctly

### Date Range Mode

-   [ ] Can select start date
-   [ ] End date minimum is start date
-   [ ] Cannot select end date before start date
-   [ ] Range displays as "MM/DD/YYYY - MM/DD/YYYY"
-   [ ] Form submission works correctly

### Multiple Dates Mode

-   [ ] Can add multiple dates
-   [ ] Dates are sorted chronologically
-   [ ] Cannot add duplicate dates
-   [ ] Can remove individual dates
-   [ ] Dates display as comma-separated list
-   [ ] Form submission works correctly

### General Validation

-   [ ] Modal opens and closes properly
-   [ ] Preview updates in real-time
-   [ ] Required field validation works
-   [ ] Data saves to database correctly
-   [ ] Medical certificate PDF shows correct dates

---

## 🐛 Known Issues & Limitations

1. **PDF Generation:** May need to update PDF template to handle multiple date formats
2. **Database Column:** `examined_on` field should support longer text (not just DATE type)
3. **Editing:** Edit form needs similar date selector for consistency

---

## 📊 Database Considerations

### Current Column Type

Check if `examined_on` column can store text data:

```sql
-- Check current structure
DESCRIBE request_documents;

-- If needed, modify column type
ALTER TABLE request_documents
MODIFY COLUMN examined_on VARCHAR(255);
```

### Recommended Type

-   **Type:** `VARCHAR(255)` or `TEXT`
-   **Reason:** Support for date ranges and multiple dates (not just single DATE)

---

## 🔄 Future Enhancements

1. **Edit Form Integration**

    - Add same date selector to edit form
    - Parse stored format back to modal

2. **PDF Template Update**

    - Format date ranges properly in certificate
    - Handle multiple dates display

3. **Validation Rules**

    - Add server-side validation for date formats
    - Validate date range logic

4. **Date Parsing Helper**
    - Create helper function to parse stored dates
    - Use in views and PDF generation

---

## 📚 Code Snippets

### Checking Date Format Type

```php
// In controller or helper
function parseExaminedOnDate($dateString) {
    if (strpos($dateString, '|range') !== false) {
        // Date range format
        $parts = explode('|', $dateString);
        return [
            'type' => 'range',
            'start' => $parts[0],
            'end' => $parts[1]
        ];
    } elseif (strpos($dateString, '|multiple') !== false) {
        // Multiple dates format
        $parts = explode('|', $dateString);
        $dates = explode(',', $parts[0]);
        return [
            'type' => 'multiple',
            'dates' => $dates
        ];
    } else {
        // Single date format
        return [
            'type' => 'single',
            'date' => $dateString
        ];
    }
}
```

### Display in View

```php
@php
    $dateInfo = parseExaminedOnDate($certificate->examined_on);

    if ($dateInfo['type'] === 'range') {
        $displayDate = date('m/d/Y', strtotime($dateInfo['start'])) . ' - ' .
                      date('m/d/Y', strtotime($dateInfo['end']));
    } elseif ($dateInfo['type'] === 'multiple') {
        $formattedDates = array_map(function($d) {
            return date('m/d/Y', strtotime($d));
        }, $dateInfo['dates']);
        $displayDate = implode(', ', $formattedDates);
    } else {
        $displayDate = date('m/d/Y', strtotime($dateInfo['date']));
    }
@endphp

{{ $displayDate }}
```

---

## ✨ Benefits

1. **Flexibility** - Accommodate different examination scenarios
2. **Accuracy** - Record exact dates of multiple visits
3. **Compliance** - Better documentation for medical records
4. **User-Friendly** - Intuitive modal interface
5. **Professional** - Enhanced functionality for clinic staff

---

_Feature completed: October 16, 2025_  
_Status: ✅ Implemented and Ready for Testing_
