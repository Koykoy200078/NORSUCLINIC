document.addEventListener("DOMContentLoaded", loadMedicineCreateData);

("use strict");

function loadMedicineCreateData() {
    $("#medicineCategoryId,#medicineBrandId").select2({
        width: "100%",
    });
    listenClick(".showMedicineBtn", function (event) {
        event.preventDefault();
        let medicineId = $(event.currentTarget).attr("data-id");
        renderMedicineData(medicineId);
    });

    function renderMedicineData(id) {
        $.ajax({
            url: route("medicines.show.modal", id),
            type: "GET",
            success: function (result) {
                if (result.success) {
                    $("#showMedicineName").text(result.data.name);
                    $("#showMedicineBrand").text(result.data.brand_name);
                    $("#showMedicineCategory").text(result.data.category_name);
                    $("#showMedicineSaltComposition").text(
                        result.data.salt_composition
                    );
                    $("#showMedicineSellingPrice").text(
                        result.data.selling_price
                    );
                    $("#showMedicineBuyingPrice").text(
                        result.data.buying_price
                    );
                    $("#showMedicineMinStockAlert").text(
                        result.data.minimum_stock_alert
                            ? result.data.minimum_stock_alert
                            : "Not set"
                    );
                    $("#showMedicineStockAlertPercentage").text(
                        result.data.stock_alert_percentage
                            ? result.data.stock_alert_percentage + "%"
                            : "Not set"
                    );
                    $("#showMedicineQuanity").text(
                        addCommas(result.data.quantity)
                    );
                    $("#showMedicineAvailableQuanity").text(
                        addCommas(result.data.available_quantity)
                    );
                    $("#showMedicineSideEffects").text(
                        result.data.side_effects
                    );
                    moment.locale($("#medicineLanguage").val());
                    let createDate = moment(result.data.created_at);
                    $("#showMedicineCreatedOn").text(createDate.fromNow());
                    $("#showMedicineUpdatedOn").text(
                        moment(result.data.updated_at).fromNow()
                    );
                    $("#showMedicineDescription").text(result.data.description);

                    // Populate dosage table
                    let dosageTableBody = $("#showMedicineDosageTable");
                    dosageTableBody.empty();

                    if (
                        result.data.purchased_medicines &&
                        result.data.purchased_medicines.length > 0
                    ) {
                        result.data.purchased_medicines.forEach(function (
                            item
                        ) {
                            // Format remaining days with color coding
                            let remainingDaysHtml = "N/A";
                            let rowClass = "";
                            let expiryDateDisplay = item.expiry_date || "N/A";

                            if (
                                item.remaining_days !== null &&
                                item.remaining_days !== undefined
                            ) {
                                if (item.remaining_days < 0) {
                                    // Expired
                                    remainingDaysHtml = `<span class="badge bg-danger">Expired (${Math.abs(
                                        item.remaining_days
                                    )} days ago)</span>`;
                                    rowClass = "table-danger";
                                } else if (item.remaining_days === 0) {
                                    // Expires today
                                    remainingDaysHtml = `<span class="badge bg-danger">Expires Today</span>`;
                                    rowClass = "table-danger";
                                } else if (item.remaining_days <= 30) {
                                    // Critical: 30 days or less
                                    remainingDaysHtml = `<span class="badge bg-warning text-dark">${item.remaining_days} days</span>`;
                                    rowClass = "table-warning";
                                } else if (item.remaining_days <= 90) {
                                    // Warning: 90 days or less
                                    remainingDaysHtml = `<span class="badge bg-info">${item.remaining_days} days</span>`;
                                } else {
                                    // Good: more than 90 days
                                    remainingDaysHtml = `<span class="badge bg-success">${item.remaining_days} days</span>`;
                                }
                            }

                            let row = `<tr class="${rowClass}">
                                <td>${item.dosage}</td>
                                <td>${addCommas(item.quantity)}</td>
                                <td>${expiryDateDisplay}</td>
                                <td>${remainingDaysHtml}</td>
                            </tr>`;
                            dosageTableBody.append(row);
                        });
                    } else {
                        console.warn("No purchased medicines data found"); // Debug log
                        dosageTableBody.html(
                            '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>'
                        );
                    }

                    setValueOfEmptySpan();
                    $("#showMedicine").appendTo("body").modal("show");
                }
            },
            error: function (result) {
                displayErrorMessage(result.responseJSON.message);
            },
        });
    }
}

listenClick(".deleteMedicineBtn", function (event) {
    let id = $(event.currentTarget).attr("data-id");
    medicineDeleteItem(
        route("check.use.medicine", id),
        Lang.get("js.medicine")
    );
});

window.medicineDeleteItem = function (url, header) {
    var tableId = null;
    var callFunction = null;
    $.ajax({
        url: url,
        type: "GET",
        success: function (result) {
            if (result.success) {
                let popUpText =
                    result.data.result == true
                        ? Lang.get("js.the_medicine_already_in_use")
                        : Lang.get("js.are_you_sure") + ' "' + header + '"?';
                swal({
                    title: Lang.get("js.deleted"),
                    text: popUpText,
                    icon: "warning",
                    buttons: {
                        confirm: Lang.get("js.yes"),
                        cancel: Lang.get("js.no"),
                    },
                }).then((popResult) => {
                    if (popResult) {
                        deleteMedicineAjax(
                            $("#indexMedicineUrl").val() + "/" + result.data.id,
                            (tableId = null),
                            header,
                            (callFunction = null)
                        );
                    }
                });
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
};

function deleteMedicineAjax(url, tableId = null, header, callFunction = null) {
    $.ajax({
        url: url,
        type: "DELETE",
        dataType: "json",
        success: function (obj) {
            if (obj.success && obj.data) {
                swal({
                    title: obj.message,
                    text: Lang.get("js.are_you_sure") + ' "' + header + '"?',
                    icon: sweetAlertIcon,
                    timer: 3000,
                    buttons: {
                        confirm: Lang.get("js.yes"),
                        cancel: Lang.get("js.no"),
                    },
                }).then((result) => {
                    if (result) {
                        $.ajax({
                            url: url,
                            type: "DELETE",
                            dataType: "json",
                            data: { canDeleteCheck: "yes" },
                            success: function (obj) {},
                            error: function (data) {
                                swal({
                                    title: "",
                                    text: data.responseJSON.message,
                                    confirmButtonColor: "#009ef7",
                                    icon: "error",
                                    timer: 5000,
                                    buttons: {
                                        confirm: Lang.get("js.ok"),
                                    },
                                });
                            },
                        });
                    }
                });
            }
            if (obj.success && !obj.data) {
                Livewire.dispatch("resetPage");
                swal({
                    icon: "success",
                    title: Lang.get("js.deleted"),
                    confirmButtonColor: "#f62947",
                    text: header + " " + Lang.get("js.has_been"),
                    timer: 2000,
                    buttons: {
                        confirm: Lang.get("js.ok"),
                    },
                });
                if (callFunction) {
                    eval(callFunction);
                }
            }
        },
        error: function (data) {
            swal({
                title: "",
                text: data.responseJSON.message,
                confirmButtonColor: "#009ef7",
                icon: "error",
                timer: 5000,
                buttons: {
                    confirm: Lang.get("js.ok"),
                },
            });
        },
    });
}
