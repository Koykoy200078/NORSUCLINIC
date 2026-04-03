document.addEventListener("DOMContentLoaded", loadMedicineAvailabilityCreate);
let uniquePrescriptionId = "";

// Helper function to initialize flatpickr with specific format
function initializeFlatpickrForElement(
    element,
    format,
    allowPastDates = false,
    dateType = "general", // 'manufacturing', 'expiry', or 'general'
) {
    let config = {};

    // Manufacturing dates can be in the past (no minDate restriction)
    // Expiry dates should be validated against manufacturing date
    if (dateType === "expiry" && !allowPastDates) {
        // Get the row ID to find corresponding manufacturing date
        let rowId = $(element).attr("id").replace("expiry_date", "");
        let manufacturingDate = $("#manufacturing_date" + rowId).val();

        if (manufacturingDate) {
            // Set minDate to the day after manufacturing date
            let minDate = new Date(manufacturingDate);
            minDate.setDate(minDate.getDate() + 1);
            config.minDate = minDate;
        } else {
            // If no manufacturing date set, default to today
            config.minDate = new Date();
        }
    } else if (dateType === "general" && !allowPastDates) {
        // For other date types, use default minDate behavior
        config.minDate = new Date();
    }
    // Manufacturing dates (dateType === 'manufacturing') have no minDate restriction

    // For month-only format, configure to show only month and year
    if (format === "Y-m") {
        // Save as Y-m format (without day)
        config.dateFormat = "Y-m";
        config.altInput = true;
        config.altFormat = "F Y";
        config.enableTime = false;

        // Set default date if element has value
        if ($(element).val()) {
            let existingValue = $(element).val();
            // Parse Y-m format and set to first day of month for display
            config.defaultDate = existingValue + "-01";
        }

        config.onReady = function (selectedDates, dateStr, instance) {
            // Add custom class for month-only mode
            instance.calendarContainer.classList.add("flatpickr-monthSelect");
        };

        config.onMonthChange = function (selectedDates, dateStr, instance) {
            // Auto-save when month/year changes - no need to click a day
            let currentYear = instance.currentYear;
            let currentMonth = String(instance.currentMonth + 1).padStart(
                2,
                "0",
            );
            let yearMonth = `${currentYear}-${currentMonth}`;

            // Set the date to first day of selected month for display purposes
            let firstDayOfMonth = new Date(
                currentYear,
                instance.currentMonth,
                1,
            );
            instance.setDate(firstDayOfMonth, false); // false = don't trigger onChange

            // Update the actual input value to Y-m format
            instance.element.value = yearMonth;

            // Close the calendar after saving
            setTimeout(() => {
                instance.close();
            }, 100);
        };

        config.onChange = function (selectedDates, dateStr, instance) {
            // Fallback for direct date clicks
            if (selectedDates.length > 0) {
                let date = selectedDates[0];
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, "0");
                instance.element.value = `${year}-${month}`;

                setTimeout(() => {
                    instance.close();
                }, 50);
            }
        };
    } else {
        config.dateFormat = "Y-m-d";

        // Set default date if element has value
        if ($(element).val()) {
            config.defaultDate = $(element).val();
        }
    }

    $(element).flatpickr(config);
}

// Initialize all flatpickr instances
function initializeAllFlatpickrs() {
    // Check if we're on edit page by looking for existing medicine IDs
    let isEditPage = $("input[name='purchased_medicine_id[]']").length > 0;

    // Initialize manufacturing date pickers (allow past dates)
    $(".purchaseMedicineManufacturingDate").each(function () {
        let rowId = $(this).attr("id").replace("manufacturing_date", "");
        let format = $("#manufacturing_format" + rowId).val() || "Y-m-d";
        initializeFlatpickrForElement(this, format, true, "manufacturing"); // Always allow past dates for manufacturing
    });

    // Initialize expiry date pickers (validate against manufacturing date)
    $(".purchaseMedicineExpiryDate").each(function () {
        let rowId = $(this).attr("id").replace("expiry_date", "");
        let format = $("#expiry_format" + rowId).val() || "Y-m-d";
        initializeFlatpickrForElement(this, format, isEditPage, "expiry");
    });
}

function loadMedicineAvailabilityCreate() {
    if (!"#purchaseUniqueId".length) {
        return;
    }

    // Initialize all flatpickr instances
    initializeAllFlatpickrs();

    // Handle toggle switch changes for manufacturing date format
    $(document).on("change", ".manufacturing-format-toggle", function () {
        let rowId = $(this).data("id");
        let isChecked = $(this).is(":checked");

        // Update format: checked = Y-m (Month Only), unchecked = Y-m-d (Full Date)
        let format = isChecked ? "Y-m" : "Y-m-d";

        // Update hidden input value
        $("#manufacturing_format" + rowId).val(format);

        // Update title attribute for tooltip
        $(this).attr(
            "title",
            isChecked ? "Month Only (Y-M)" : "Full Date (Y-M-D)",
        );

        // Reinitialize flatpickr with new format
        let manufacturingInput = $("#manufacturing_date" + rowId);
        let isEditPage = $("input[name='purchased_medicine_id[]']").length > 0;

        // Destroy existing flatpickr instance
        if (manufacturingInput[0]._flatpickr) {
            manufacturingInput[0]._flatpickr.destroy();
        }

        // Reinitialize with new format (manufacturing dates allow past dates)
        initializeFlatpickrForElement(
            manufacturingInput,
            format,
            true,
            "manufacturing",
        );

        // Update corresponding expiry date minDate when manufacturing date changes
        updateExpiryMinDate(rowId);
    });

    // Handle manufacturing date changes to update expiry date validation
    $(document).on("change", ".purchaseMedicineManufacturingDate", function () {
        let rowId = $(this).attr("id").replace("manufacturing_date", "");
        updateExpiryMinDate(rowId);
    });

    // Handle toggle switch changes for expiry date format
    $(document).on("change", ".expiry-format-toggle", function () {
        let rowId = $(this).data("id");
        let isChecked = $(this).is(":checked");

        // Update format: checked = Y-m (Month Only), unchecked = Y-m-d (Full Date)
        let format = isChecked ? "Y-m" : "Y-m-d";

        // Update hidden input value
        $("#expiry_format" + rowId).val(format);

        // Update title attribute for tooltip
        $(this).attr(
            "title",
            isChecked ? "Month Only (Y-M)" : "Full Date (Y-M-D)",
        );

        // Reinitialize flatpickr with new format
        let expiryInput = $("#expiry_date" + rowId);
        let isEditPage = $("input[name='purchased_medicine_id[]']").length > 0;

        // Destroy existing flatpickr instance
        if (expiryInput[0]._flatpickr) {
            expiryInput[0]._flatpickr.destroy();
        }

        // Reinitialize with new format (validate against manufacturing date)
        initializeFlatpickrForElement(
            expiryInput,
            format,
            isEditPage,
            "expiry",
        );
    });

    // Helper function to update expiry date minDate based on manufacturing date
    function updateExpiryMinDate(rowId) {
        let manufacturingDate = $("#manufacturing_date" + rowId).val();
        let expiryInput = $("#expiry_date" + rowId);

        if (expiryInput[0]._flatpickr && manufacturingDate) {
            let minDate = new Date(manufacturingDate);
            minDate.setDate(minDate.getDate() + 1);
            expiryInput[0]._flatpickr.set("minDate", minDate);
        }
    }

    // Disabled Select2 for paymentMode since it's now a hidden field
    // $("#paymentMode,#paymentMode2").select2({
    //     width: "100%",
    // });
}

listenClick(".add-medicine-btn-purchase", function () {
    uniquePrescriptionId = $("#purchaseUniqueId").val();
    let data = {
        medicines: JSON.parse($(".associatePurchaseMedicines").val()),
        uniqueId: uniquePrescriptionId,
    };
    let prescriptionMedicineHtml = prepareTemplateRender(
        "#purchaseMedicineTemplate",
        data,
    );
    $(".prescription-medicine-container").append(prescriptionMedicineHtml);
    dropdownToSelecte2(".purchaseMedicineId");

    // Initialize flatpickr for manufacturing date in the newly added row (allow past dates)
    let isEditPage = $("input[name='purchased_medicine_id[]']").length > 0;
    let manufacturingFormat =
        $("#manufacturing_format" + uniquePrescriptionId).val() || "Y-m-d";
    initializeFlatpickrForElement(
        $("#manufacturing_date" + uniquePrescriptionId),
        manufacturingFormat,
        true,
        "manufacturing",
    );

    // Initialize flatpickr for the newly added expiry date row (validate against manufacturing)
    let expiryFormat =
        $("#expiry_format" + uniquePrescriptionId).val() || "Y-m-d";
    initializeFlatpickrForElement(
        $("#expiry_date" + uniquePrescriptionId),
        expiryFormat,
        isEditPage,
        "expiry",
    );

    uniquePrescriptionId++;
    $("#purchaseUniqueId").val(uniquePrescriptionId);
});
const dropdownToSelecte2 = (selector) => {
    $(selector).select2({
        placeholder: Lang.get("js.select_medicine"),
        width: "100%",
    });
};

listenChange(".purchaseMedicineId", function () {
    let medicineId = $(this).val();

    if (medicineId == "") {
        return false;
    }

    // No need to fetch prices since we're not tracking them
    // Medicine selection is just for availability tracking
});

// No calculation needed - removed tax, discount, price calculations
// Medicine availability only tracks quantity, dates, and dosage

listenClick(".delete-medicine-availability-item", function () {
    $(this).parents("tr").remove();
});

listenSubmit("#purchaseMedicineFormId", function (e) {
    e.preventDefault();

    let y = $("#purchaseUniqueId").val() - 1;

    for (let i = 1; i <= y; i++) {
        let medicinID = "#medicineChooseId" + i;

        if (typeof $(medicinID).val() != "undefined") {
            if ($(medicinID).val() == null || $(medicinID).val() == "") {
                displayErrorMessage("Select medicine.");
                return false;
            }
        }

        let manufacturingDate = "#manufacturing_date" + i;
        if (typeof $(manufacturingDate).val() != "undefined") {
            if (
                $(manufacturingDate).val() == null ||
                $(manufacturingDate).val() == ""
            ) {
                displayErrorMessage("Enter manufacturing date.");
                return false;
            }
        }

        let quantityID = "#quantity" + i;
        if (typeof $(quantityID).val() != "undefined") {
            if ($(quantityID).val() == null || $(quantityID).val() == "") {
                displayErrorMessage("Enter quantity.");
                return false;
            } else if ($(quantityID).val() == 0) {
                displayErrorMessage("Quantity should be greater than 0.");
                return false;
            }
        }
    }

    $(this)[0].submit();
});

listenClick(".medicineAvailabilityDelete", function (event) {
    let id = $(event.currentTarget).attr("data-id");
    deleteItem(
        route("medicine-availability.destroy", id),
        Lang.get("js.medicine_availability"),
    );
});
