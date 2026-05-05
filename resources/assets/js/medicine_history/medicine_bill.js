document.addEventListener("DOMContentLoaded", loadSaleMedicineCreate);
let uniquePrescriptionId = "";
const MEDICINE_PLACEHOLDER = "Select Medicine";
const DOSAGE_PLACEHOLDER = "Select Dosage/Strength";

function loadSaleMedicineCreate() {
    if (!$("#medicineUniqueId").length) {
        return;
    }

    dropdownToSelecte2(".medicinePurchaseId");
    dropdownToSelecteCategories2(".medicineBillCategoriesId");
    dropdownToSelectDosage2(".medicineBillDosage");

    $(".medicine_bill_date").flatpickr({
        enableTime: true,
        defaultDate: new Date(),
        dateFormat: "Y-m-d H:i",
    });

    $(".edit_medicine_bill_date").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    expiryDateFlatePicker(".medicineBillExpiryDate");
    initializeExistingMedicineRows();
}

function initializeExistingMedicineRows() {
    $(".medicine-bill-container tr").each(function () {
        const currentRow = $(this);
        const categoryId = currentRow.find(".medicineBillCategoriesId").val();
        const selectedMedicineId = currentRow.find(".purchaseMedicineId").val();
        const dosageSelect = currentRow.find(".medicineBillDosage");
        const selectedDosage =
            dosageSelect.data("selected-dosage") || dosageSelect.val() || "";

        if (categoryId) {
            loadMedicinesByCategory(
                currentRow,
                categoryId,
                selectedMedicineId,
                selectedDosage,
            );
        }
    });
}

function resetRowMedicineFields(currentRow) {
    const medicineSelect = currentRow.find(".purchaseMedicineId");
    const dosageSelect = currentRow.find(".medicineBillDosage");
    const quantityInput = currentRow.find(".medicineBill-quantity");

    medicineSelect.find("option").remove();
    medicineSelect.append(
        $("<option></option>").attr("value", "").text(MEDICINE_PLACEHOLDER),
    );
    medicineSelect.val("").trigger("change.select2");

    dosageSelect.find("option").remove();
    dosageSelect.append(
        $("<option></option>").attr("value", "").text(DOSAGE_PLACEHOLDER),
    );
    dosageSelect.val("").trigger("change.select2");
    dosageSelect.removeData("selected-dosage");

    setRowExpiryDate(currentRow, "");
    quantityInput.removeAttr("max");
}

function setRowExpiryDate(currentRow, expiryDate) {
    const expiryInput = currentRow.find(".medicineBillExpiryDate");

    if (!expiryInput.length) {
        return;
    }

    if (expiryInput[0]._flatpickr) {
        if (expiryDate) {
            expiryInput[0]._flatpickr.setDate(expiryDate, true, "Y-m-d");
        } else {
            expiryInput[0]._flatpickr.clear();
        }
    } else {
        expiryInput.val(expiryDate || "");
    }
}

function getDosagesFromOption(option) {
    let dosages = option.data("dosages") || [];

    if (typeof dosages === "string") {
        try {
            dosages = JSON.parse(dosages);
        } catch (e) {
            dosages = [];
        }
    }

    return Array.isArray(dosages) ? dosages : [];
}

function renderMedicineOptions(
    medicineSelect,
    medicines,
    selectedMedicineId = "",
) {
    medicineSelect.find("option").remove();
    medicineSelect.append(
        $("<option></option>").attr("value", "").text(MEDICINE_PLACEHOLDER),
    );

    $.each(medicines, function (_index, medicine) {
        const option = $("<option></option>")
            .attr("value", medicine.id)
            .text(medicine.name);

        option.data(
            "dosages",
            Array.isArray(medicine.dosages) ? medicine.dosages : [],
        );
        medicineSelect.append(option);
    });

    if (selectedMedicineId) {
        medicineSelect.val(String(selectedMedicineId));
    }

    medicineSelect.trigger("change.select2");
}

function loadMedicinesByCategory(
    currentRow,
    categoryId,
    selectedMedicineId = "",
    selectedDosage = "",
) {
    const medicineSelect = currentRow.find(".purchaseMedicineId");
    const dosageSelect = currentRow.find(".medicineBillDosage");

    if (!categoryId) {
        resetRowMedicineFields(currentRow);

        return;
    }

    resetRowMedicineFields(currentRow);

    $.ajax({
        type: "get",
        url: panelRoute("dispense-records.by-category", categoryId),
        success: function (result) {
            let medicines = [];

            if (
                result &&
                result.data &&
                Array.isArray(result.data.medicine_details)
            ) {
                medicines = result.data.medicine_details;
            } else if (result && result.data && result.data.medicine) {
                $.each(result.data.medicine, function (id, name) {
                    medicines.push({
                        id: id,
                        name: name,
                        dosages: [],
                    });
                });
            }

            renderMedicineOptions(
                medicineSelect,
                medicines,
                selectedMedicineId,
            );

            if (selectedDosage) {
                dosageSelect.data("selected-dosage", selectedDosage);
            }

            if (selectedMedicineId) {
                populateDosageForRow(currentRow);
            }
        },
    });
}

function populateDosageForRow(currentRow) {
    const medicineSelect = currentRow.find(".purchaseMedicineId");
    const dosageSelect = currentRow.find(".medicineBillDosage");
    const quantityInput = currentRow.find(".medicineBill-quantity");
    const selectedMedicineId = medicineSelect.val();

    dosageSelect.find("option").remove();
    dosageSelect.append(
        $("<option></option>").attr("value", "").text(DOSAGE_PLACEHOLDER),
    );
    dosageSelect.val("").trigger("change.select2");
    quantityInput.removeAttr("max");
    setRowExpiryDate(currentRow, "");

    if (
        !selectedMedicineId ||
        selectedMedicineId === Lang.get("js.select_medicine")
    ) {
        return;
    }

    const selectedOption = medicineSelect.find("option:selected");
    const dosages = getDosagesFromOption(selectedOption);

    $.each(dosages, function (_index, dosageInfo) {
        const dosageValue = String(dosageInfo.dosage || "N/A");
        const availableQuantity = Number(dosageInfo.available_quantity || 0);
        const expiryDate = dosageInfo.expiry_date || "";
        const optionLabel =
            dosageValue +
            (availableQuantity > 0 ? " (Avl: " + availableQuantity + ")" : "");

        dosageSelect.append(
            $("<option></option>")
                .attr("value", dosageValue)
                .attr("data-available-quantity", availableQuantity)
                .attr("data-expiry-date", expiryDate)
                .text(optionLabel),
        );
    });

    const preferredDosage = dosageSelect.data("selected-dosage") || "";
    const hasPreferredOption =
        dosageSelect.find("option").filter(function () {
            return $(this).val() === preferredDosage;
        }).length > 0;

    if (preferredDosage && hasPreferredOption) {
        dosageSelect.val(preferredDosage);
    } else if (dosages.length === 1) {
        dosageSelect.val(String(dosages[0].dosage || "N/A"));
    }

    dosageSelect.trigger("change.select2");
    dosageSelect.removeData("selected-dosage");

    if (dosageSelect.val()) {
        dosageSelect.trigger("change");
    }
}

function updateRowDosageSelection(currentRow) {
    const dosageSelect = currentRow.find(".medicineBillDosage");
    const quantityInput = currentRow.find(".medicineBill-quantity");
    const selectedOption = dosageSelect.find("option:selected");

    if (!selectedOption.length || !dosageSelect.val()) {
        quantityInput.removeAttr("max");
        setRowExpiryDate(currentRow, "");

        return;
    }

    const expiryDate = selectedOption.attr("data-expiry-date") || "";
    const availableQuantity = Number(
        selectedOption.attr("data-available-quantity") || 0,
    );

    setRowExpiryDate(currentRow, expiryDate);

    if (availableQuantity > 0) {
        quantityInput.attr("max", availableQuantity);

        if (Number(quantityInput.val()) > availableQuantity) {
            quantityInput.val(availableQuantity);
            displayErrorMessage(
                "Only " +
                    availableQuantity +
                    " item(s) are available for the selected strength.",
            );
        }
    } else {
        quantityInput.removeAttr("max");
    }
}

listenChange(".medicineBillCategoriesId", function () {
    const categoryId = $(this).val();
    const currentRow = $(this).closest("tr");

    loadMedicinesByCategory(currentRow, categoryId);
});

listenChange(".medicinePurchaseId", function () {
    const currentRow = $(this).closest("tr");
    currentRow.find(".medicineBillDosage").removeData("selected-dosage");

    populateDosageForRow(currentRow);
});

listenChange(".medicineBillDosage", function () {
    const currentRow = $(this).closest("tr");
    updateRowDosageSelection(currentRow);
});

listenClick(".add-medicine-btn-medicine-bill", function () {
    uniquePrescriptionId = $("#medicineUniqueId").val();
    let data = {
        medicinesCategories: JSON.parse(
            $("#showMedicineCategoriesMedicineBill").val(),
        ),
        medicines: JSON.parse($(".associatePurchaseMedicines").val()),
        uniqueId: uniquePrescriptionId,
    };
    let prescriptionMedicineHtml = prepareTemplateRender(
        "#medicineBillTemplate",
        data,
    );
    $(".medicine-bill-container").append(prescriptionMedicineHtml);

    const latestRow = $(".medicine-bill-container tr:last");
    dropdownToSelecte2(latestRow.find(".medicinePurchaseId"));
    dropdownToSelecteCategories2(latestRow.find(".medicinebillCategories"));
    dropdownToSelectDosage2(latestRow.find(".medicineBillDosage"));
    expiryDateFlatePicker(latestRow.find(".medicineBillExpiryDate"));

    uniquePrescriptionId++;
    $("#medicineUniqueId").val(uniquePrescriptionId);
});

const dropdownToSelecte2 = (selector) => {
    $(selector).select2({
        placeholder: MEDICINE_PLACEHOLDER,
        width: "100%",
    });
};

const dropdownToSelecteCategories2 = (selector) => {
    $(selector).select2({
        placeholder: Lang.get("js.select_category"),
        width: "100%",
    });
};

const dropdownToSelectDosage2 = (selector) => {
    $(selector).select2({
        placeholder: DOSAGE_PLACEHOLDER,
        width: "100%",
    });
};

const expiryDateFlatePicker = (selector) => {
    $(selector).prop("readonly", true);
    $(selector).flatpickr({
        minDate: new Date(),
        dateFormat: "Y-m-d",
        clickOpens: false,
        allowInput: false,
    });
};

listenKeyup(".medicineBill-quantity", function () {
    let value = String($(this).val() || "").replace(/[^0-9]/g, "");

    if (value === "" || Number(value) < 1) {
        value = "1";
    }

    const max = Number($(this).attr("max") || 0);
    if (max > 0 && Number(value) > max) {
        value = String(max);
    }

    $(this).val(value);
});

listenChange(".medicineBill-quantity", function () {
    const max = Number($(this).attr("max") || 0);
    const currentValue = Number($(this).val() || 0);

    if (max > 0 && currentValue > max) {
        $(this).val(max);
    }

    if (currentValue < 1) {
        $(this).val(1);
    }
});

function hasInvalidQuantity() {
    let invalidQuantityFound = false;

    $(".medicineBill-quantity").each(function () {
        const value = Number($(this).val());
        const max = Number($(this).attr("max") || 0);

        if (!value || value < 1 || (max > 0 && value > max)) {
            invalidQuantityFound = true;
            return false;
        }
    });

    return invalidQuantityFound;
}

listenSubmit("#CreateMedicineBillForm", function (e) {
    e.preventDefault();

    if (hasInvalidQuantity()) {
        displayErrorMessage(Lang.get("js.quantity_should"));
        return false;
    }

    if ($(this).closest(".modal").length) {
        let form = this;
        let loadingBtn = $("#dispenseSaveBtn");
        loadingBtn.prop("disabled", true);
        $.ajax({
            url: $(form).attr("action"),
            type: "POST",
            data: $(form).serialize(),
            success: function (result) {
                if (result.success) {
                    displaySuccessMessage(result.message);
                    $("#add_dispense_modal").modal("hide");
                    Livewire.dispatch("refresh");
                }
            },
            error: function (result) {
                displayErrorMessage(result.responseJSON.message);
            },
            complete: function () {
                loadingBtn.prop("disabled", false);
            },
        });
    } else {
        $(this)[0].submit();
    }
});

listen("hidden.bs.modal", "#add_dispense_modal", function () {
    let form = $("#CreateMedicineBillForm")[0];
    if (form) form.reset();
    $(".medicine-bill-container tr:not(:first)").remove();
    $("#medicineUniqueId").val(2);

    const firstRow = $(".medicine-bill-container tr:first");
    if (firstRow.length) {
        firstRow
            .find(".medicineBillCategoriesId")
            .val("")
            .trigger("change.select2");
        resetRowMedicineFields(firstRow);
        firstRow.find(".medicineBill-quantity").val(1).removeAttr("max");
    }
});

listenClick(".add-patient-modal", function () {
    $("#addPatientModal").appendTo("body").modal("show");
});

listenSubmit("#addPatientForm", function (e) {
    e.preventDefault();
    processingBtn("#addPatientForm", "#patientBtnSave", "loading");
    $("#patientBtnSave").attr("disabled", true);
    $.ajax({
        url: panelRoute("dispense-records.store-patient"),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                $("#prescriptionPatientId").find("option").remove();
                $("#prescriptionPatientId").append(
                    $("<option></option>")
                        .attr("placeholder", "")
                        .text(Lang.get("js.select_patient")),
                );
                $.each(result.data, function (i, v) {
                    $("#prescriptionPatientId").append(
                        $("<option></option>").attr("value", i).text(v),
                    );
                });
                displaySuccessMessage(result.message);
                $("#addPatientModal").modal("hide");
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
        complete: function () {
            $("#patientBtnSave").attr("disabled", false);
            processingBtn("#addPatientForm", "#patientBtnSave");
        },
    });
});

listen("hidden.bs.modal", "#addPatientModal", function () {
    resetModalForm("#addPatientForm", "#patientErrorsBox");
});

listenClick(".medicine-bill-delete-btn", function (event) {
    let id = $(event.currentTarget).attr("data-id");

    deleteItem(panelRoute("dispense-records.destroy", id), "");
});

listenSubmit("#MedicinebillForm", function (e) {
    e.preventDefault();

    if (hasInvalidQuantity()) {
        displayErrorMessage(Lang.get("js.quantity_should"));
        return false;
    }

    const medicineBillId = $("#medicineBillId").val();

    $.ajax({
        url: panelRoute("dispense-records.update", medicineBillId),
        type: "post",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                setTimeout(function () {
                    // Turbo.visit(route("medicine-history.index")); // true
                    window.location.href = panelRoute(
                        "medicine-dispensing.index",
                    );
                }, 2000);
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".delete-medicine-bill-item", function () {
    $(this).parents("tr").remove();
});
