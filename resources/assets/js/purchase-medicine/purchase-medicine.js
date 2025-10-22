document.addEventListener("DOMContentLoaded", loadPurchaseMedicineCreate);
let uniquePrescriptionId = "";

// Helper function to initialize flatpickr with specific format
function initializeFlatpickrForElement(
    element,
    format,
    allowPastDates = false
) {
    let config = {};

    // Only set minDate for create page, not edit page
    if (!allowPastDates) {
        config.minDate = new Date();
    }

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

        config.onChange = function (selectedDates, dateStr, instance) {
            // Save as Y-m format (without day)
            if (selectedDates.length > 0) {
                let date = selectedDates[0];
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, "0");
                instance.element.value = `${year}-${month}`;
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

    $(".purchaseMedicineExpiryDate").each(function () {
        let rowId = $(this).attr("id").replace("expiry_date", "");
        let format = $("#expiry_format" + rowId).val() || "Y-m-d";
        initializeFlatpickrForElement(this, format, isEditPage);
    });
}

function loadPurchaseMedicineCreate() {
    if (!$("#purchaseUniqueId").length) {
        return;
    }

    // Initialize all flatpickr instances
    initializeAllFlatpickrs();

    // Handle format selector changes
    $(document).on("change", ".expiry-format-selector", function () {
        let rowId = $(this).data("id");
        let format = $(this).val();
        let expiryInput = $("#expiry_date" + rowId);
        let isEditPage = $("input[name='purchased_medicine_id[]']").length > 0;

        // Destroy existing flatpickr instance
        if (expiryInput[0]._flatpickr) {
            expiryInput[0]._flatpickr.destroy();
        }

        // Reinitialize with new format
        initializeFlatpickrForElement(expiryInput, format, isEditPage);
    });

    $("#paymentMode,#paymentMode2").select2({
        width: "100%",
    });
}

listenClick(".add-medicine-btn-purchase", function () {
    uniquePrescriptionId = $("#purchaseUniqueId").val();
    let data = {
        medicines: JSON.parse($(".associatePurchaseMedicines").val()),
        uniqueId: uniquePrescriptionId,
    };
    let prescriptionMedicineHtml = prepareTemplateRender(
        "#purchaseMedicineTemplate",
        data
    );
    $(".prescription-medicine-container").append(prescriptionMedicineHtml);
    dropdownToSelecte2(".purchaseMedicineId");

    // Initialize flatpickr for the newly added row
    let isEditPage = $("input[name='purchased_medicine_id[]']").length > 0;
    let format = $("#expiry_format" + uniquePrescriptionId).val() || "Y-m-d";
    initializeFlatpickrForElement(
        $("#expiry_date" + uniquePrescriptionId),
        format,
        isEditPage
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
    let uniqueId = $(this).attr("data-id");
    let salePriceId = "#sale_price" + uniqueId;
    let buyPriceId = "#purchase_price" + uniqueId;
    if (medicineId == "") {
        $(salePriceId).val("0.00");
        $(buyPriceId).val("0.00");

        return false;
    }
    $.ajax({
        type: "get",
        url: route("get-medicine", medicineId),
        success: function (result) {
            // Set sale_price to default 0.00 since field is intentionally hidden
            $(salePriceId).val("0.00");
            $(buyPriceId).val(result.data.buying_price.toFixed(2));
        },
    });
});

listenKeyup(
    ".purchase-quantity,.purchase-price,purchase-quantity,.purchase-tax,.purchase-discount",
    function () {
        let value = $(this).val();
        $(this).val(value.replace(/[^0-9\.]/g, ""));
        var currentRow = $(this).closest("tr");
        let currentqty = currentRow.find(".purchase-quantity").val();
        let price = currentRow.find(".purchase-price").val();
        let currentamount = parseFloat(price * currentqty);
        currentRow.find(".purchase-amount").val(currentamount.toFixed(2));
        let taxEle = $(".purchase-tax");
        let elements = $(".purchase-amount");
        let total = 0.0;
        let totalTax = 0;
        let netAmount = 0;
        let discount = 0;
        let amount = 0;
        for (let i = 0; i < elements.length; i++) {
            total += parseFloat(elements[i].value);
            discount = $(".purchase-discount").val();
            if (taxEle[i].value != 0 && taxEle[i].value != "") {
                if (taxEle[i].value > 99) {
                    let taxAmount = taxEle[i].value.slice(0, -1);
                    currentRow.find(".purchase-tax").val(taxAmount);
                    displayErrorMessage(Lang.get("js.tax_should_be"));
                    $("#discountAmount").val(discount);
                    return false;
                }
                totalTax += (elements[i].value * taxEle[i].value) / 100;
            } else {
                amount += parseFloat(elements[i].value);
            }
        }
        discount = discount == "" ? 0 : discount;
        netAmount = parseFloat(total) + parseFloat(totalTax);
        netAmount = parseFloat(netAmount) - parseFloat(discount);
        if (discount > total && $(this).hasClass("purchase-discount")) {
            discount = discount.slice(0, -1);
            displayErrorMessage(Lang.get("js.the_discount_shoul"));
            $("#discountAmount").val(discount);
            return false;
        }
        if (discount > total) {
            netAmount = 0;
        }

        $("#total").val(total.toFixed(2));
        $("#purchaseTaxId").val(totalTax.toFixed(2));
        $("#netAmount").val(netAmount.toFixed(2));
        // let value = $(this).val();
        // $(this).val(value.replace(/[^0-9\.]/g, ""));
        // var currentRow = $(this).closest("tr");
        // let currentqty = currentRow.find(".purchase-quantity").val();
        // let price = currentRow.find(".purchase-price").val();
        // let medicineBillTax = currentRow.find(".purchase-tax").val();
        // let currentamount = parseFloat(price * currentqty);
        // currentRow.find(".amount").val(currentamount.toFixed(2));

        // let y = $(".purchaseMedicineId").length;
        // let taxEle = $(".purchase-tax");
        // let elements = $(".amount");
        // let total = 0.0;
        // let totalTax = 0;
        // let netAmount = 0;
        // let discount = 0;
        // let amount = 0;
        // var qty = $(".purchase-quantity");

        // for (let i = 0; i < elements.length; i++) {
        //     total += parseFloat(elements[i].value);
        //     discount = $(".purchase-discount").val();
        //     let taxAmount = $(this).val();
        //     if (taxEle[i].value != 0 && taxEle[i].value != "") {
        //         if (taxEle[i].value > 99) {
        //             let taxAmount = taxEle[i].value.slice(0, -1);
        //             currentRow.find(".purchase-tax").val(taxAmount);
        //             displayErrorMessage(
        //                 Lang.get("Taxes should be less than 100%.")
        //             );
        //             $("#discountAmount").val(discount);
        //             return false;
        //         }
        //         totalTax += (elements[i].value * taxEle[i].value) / 100;
        //         amount += parseFloat(elements[i].value) + parseFloat(totalTax);
        //     } else {
        //         amount += parseFloat(elements[i].value);
        //     }
        // }
        // discount = discount == "" ? 0 : discount;
        // netAmount = parseFloat(amount) - parseFloat(discount);
        // if (discount > total && $(this).hasClass("purchase-discount")) {
        //     discount = discount.slice(0, -1);
        //     displayErrorMessage(
        //         Lang.get("The discount should be less than the total amount.")
        //     );
        //     $("#discountAmount").val(discount);
        //     return false;
        // }
        // if (discount > total) {
        //     netAmount = 0;
        // }
        // $("#total").val(total.toFixed(2));
        // $("#purchaseTaxId").val(totalTax.toFixed(2));
        // $("#netAmount").val(netAmount.toFixed(2));
    }
);

listenClick(".delete-purchase-medicine-item", function () {
    let currentRow = $(this).closest("tr");
    let currentRowAmount = currentRow.find(".purchase-amount").val();
    let currentRowTax = currentRow.find(".purchase-tax").val();
    let currentTaxAmount =
        parseFloat(currentRowAmount) * parseFloat(currentRowTax / 100);
    let updatedTax =
        parseFloat($("#purchaseTaxId").val()) - parseFloat(currentTaxAmount);

    $("#purchaseTaxId").val(updatedTax.toFixed(2));
    let updatedTotalAmount =
        parseFloat($("#total").val()) - parseFloat(currentRowAmount);
    $("#total").val(updatedTotalAmount.toFixed(2));
    let amountSubfromNetAmt =
        parseFloat(currentTaxAmount) + parseFloat(currentRowAmount);

    let updateNetAmount =
        parseFloat($("#netAmount").val()) - parseFloat(amountSubfromNetAmt);
    $("#netAmount").val(updateNetAmount.toFixed(2));
    $(this).parents("tr").remove();
});

listenSubmit("#purchaseMedicineFormId", function (e) {
    e.preventDefault();

    let y = $("#purchaseUniqueId").val() - 1;
    let tx = 1;
    for (let i = 1; i <= y; i++) {
        let medicinID = "#medicineChooseId" + i;
        let taxId = "tax" + i;

        if (typeof $(taxId).val() != "undefined") {
            if ($(taxId).val() == null || $(taxId).val() == "") {
                tx = 0;
            }
        }
        if (typeof $(medicinID).val() != "undefined") {
            if ($(medicinID).val() == null || $(medicinID).val() == "") {
                displayErrorMessage(Lang.get("js.enter_manufacturing_date"));
                return false;
            }
        }
        let manufacturingDate = "#manufacturing_date" + i;
        if (typeof $(manufacturingDate).val() != "undefined") {
            if (
                $(manufacturingDate).val() == null ||
                $(manufacturingDate).val() == ""
            ) {
                displayErrorMessage(Lang.get("js.enter_manufacturing_date"));
                return false;
            }
        }

        // Sale price validation removed - field is intentionally hidden with default value 0.00
        // let salePrice = "#sale_price" + i;
        // if (typeof $(salePrice).val() != "undefined") {
        //     if ($(salePrice).val() == null || $(salePrice).val() == "") {
        //         displayErrorMessage(Lang.get('js.enter_sale_price'));
        //         return false;
        //     }
        // }

        let purchasePrice = "#purchase_price" + i;
        if (typeof $(purchasePrice).val() != "undefined") {
            if (
                $(purchasePrice).val() == null ||
                $(purchasePrice).val() == ""
            ) {
                displayErrorMessage("Enter purchase price.");
                return false;
            } else if ($(purchasePrice).val() == 0) {
                displayErrorMessage(Lang.get("js.quantity_should"));
                return false;
            }
        }
        let quantityID = "#quantity" + i;
        if (typeof $(quantityID).val() != "undefined") {
            if ($(quantityID).val() == null || $(quantityID).val() == "") {
                displayErrorMessage("Enter quantity.");
                return false;
            } else if ($(quantityID).val() == 0) {
                displayErrorMessage(Lang.get("js.quantity_should"));
                return false;
            }
        }
    }

    // Net amount validation removed - field is intentionally set to 0 since price fields are hidden
    // let netAmount = "#netAmount";
    // if ($(netAmount).val() == null || $(netAmount).val() == "") {
    //     displayErrorMessage(Lang.get("js.net_amount_not_empty"));
    //     return false;
    // } else if ($(netAmount).val() == 0) {
    //     displayErrorMessage(Lang.get("js.net_amount_not_zero"));
    //     return false;
    // }

    if (
        tx == 0 &&
        ($("#purchaseTaxId").val() == null || $("#purchaseTaxId").val() == "")
    ) {
        displayErrorMessage(Lang.get("js.tax_cannot_be_zero_empty"));
        return false;
    }

    $(this)[0].submit();
});

listenClick(".purchaseMedicineDelete", function (event) {
    let id = $(event.currentTarget).attr("data-id");
    deleteItem(
        route("medicine-purchase.destroy", id),
        Lang.get("js.purchase_medicine")
    );
});
