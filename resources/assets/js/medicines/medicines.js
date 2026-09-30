document.addEventListener("DOMContentLoaded", loadMedicineCreateData);

("use strict");

function loadMedicineCreateData() {
    $("#medicineCategoryId,#medicineGenericId").select2({
        width: "100%",
    });
    listenClick(".showMedicineBtn", function (event) {
        event.preventDefault();
        let medicineId = $(event.currentTarget).attr("data-id");
        renderMedicineData(medicineId);
    });

    function renderMedicineData(id) {
        $.ajax({
            url: panelRoute("medicines.show.modal", id),
            type: "GET",
            success: function (result) {
                if (result.success) {
                    $("#showMedicineName").text(result.data.name);
                    $("#showMedicineBrand").text(
                        result.data.brand_name || "N/A",
                    );
                    $("#showMedicineGeneric").text(result.data.generic_name);
                    $("#showMedicineCategory").text(result.data.category_name);
                    $("#showMedicineDosage").text(
                        result.data.dosage_summary ||
                            result.data.dosage ||
                            "N/A",
                    );
                    $("#showMedicineUom").text(result.data.uom || "N/A");
                    $("#showMedicineSku").text(result.data.sku || "N/A");
                    $("#showMedicineReorderLevel").text(
                        result.data.reorder_level !== null &&
                            result.data.reorder_level !== undefined
                            ? result.data.reorder_level
                            : "Not set",
                    );
                    $("#showMedicineQuanity").text(
                        addCommas(result.data.quantity),
                    );
                    $("#showMedicineAvailableQuanity").text(
                        addCommas(result.data.available_quantity),
                    );
                    moment.locale($("#medicineLanguage").val());
                    let createDate = moment(result.data.created_at);
                    $("#showMedicineCreatedOn").text(createDate.fromNow());
                    $("#showMedicineUpdatedOn").text(
                        moment(result.data.updated_at).fromNow(),
                    );
                    $("#showMedicineDescription").text(result.data.description);

                    const availableRows = Array.isArray(
                        result.data.available_purchased_medicines,
                    )
                        ? result.data.available_purchased_medicines
                        : Array.isArray(result.data.purchased_medicines)
                          ? result.data.purchased_medicines
                          : [];

                    const expiredRows = Array.isArray(
                        result.data.expired_purchased_medicines,
                    )
                        ? result.data.expired_purchased_medicines
                        : [];

                    renderDosageTable(
                        $("#showMedicineDosageTable"),
                        availableRows,
                        "available",
                    );
                    renderDosageTable(
                        $("#showMedicineExpiredDosageTable"),
                        expiredRows,
                        "expired",
                    );

                    setValueOfEmptySpan();
                    $("#showMedicine").appendTo("body").modal("show");
                }
            },
            error: function (result) {
                displayErrorMessage(result.responseJSON.message);
            },
        });
    }

    function renderDosageTable(tableBody, rows, stockType) {
        tableBody.empty();

        if (!Array.isArray(rows) || rows.length === 0) {
            const emptyText =
                stockType === "expired"
                    ? "No expired stock"
                    : "No data available";

            tableBody.html(
                `<tr><td colspan="4" class="text-center text-muted">${emptyText}</td></tr>`,
            );

            return;
        }

        rows.forEach(function (item) {
            let remainingDaysHtml = "N/A";
            let rowClass = "";
            const expiryDateDisplay = item.expiry_date || "N/A";

            if (
                item.remaining_days !== null &&
                item.remaining_days !== undefined
            ) {
                if (stockType === "expired" || item.remaining_days < 0) {
                    remainingDaysHtml = `<span class="badge bg-danger">Expired (${Math.abs(
                        item.remaining_days,
                    )} days ago)</span>`;
                    rowClass = "table-danger";
                } else if (item.remaining_days === 0) {
                    remainingDaysHtml = `<span class="badge bg-danger">Expires Today</span>`;
                    rowClass = "table-danger";
                } else if (item.remaining_days <= 30) {
                    remainingDaysHtml = `<span class="badge bg-warning text-dark">${item.remaining_days} days</span>`;
                    rowClass = "table-warning";
                } else if (item.remaining_days <= 90) {
                    remainingDaysHtml = `<span class="badge bg-info">${item.remaining_days} days</span>`;
                } else {
                    remainingDaysHtml = `<span class="badge bg-success">${item.remaining_days} days</span>`;
                }
            }

            const row = `<tr class="${rowClass}">
                <td>${item.dosage}</td>
                <td>${addCommas(item.quantity)}</td>
                <td>${expiryDateDisplay}</td>
                <td>${remainingDaysHtml}</td>
            </tr>`;

            tableBody.append(row);
        });
    }
}

listenClick(".deleteMedicineBtn", function (event) {
    let id = $(event.currentTarget).attr("data-id");
    medicineDeleteItem(
        panelRoute("check.use.medicine", id),
        Lang.get("js.medicine"),
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
                // A medicine that already has stock / dispensing / prescription history cannot be
                // deleted (the server refuses too); tell the user instead of offering "Yes".
                if (result.data.result == true) {
                    swal({
                        title: Lang.get("js.deleted"),
                        text: Lang.get("js.the_medicine_already_in_use"),
                        icon: "warning",
                        button: Lang.get("js.ok") || "OK",
                    });

                    return;
                }

                let popUpText =
                    Lang.get("js.are_you_sure") + ' "' + header + '"?';
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
                            (callFunction = null),
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

listenSubmit("#addMedicineForm", function (e) {
    e.preventDefault();
    let loadingBtn = $(this).find("#medicineSaveModalBtn");
    loadingBtn.prop("disabled", true);
    $.ajax({
        url: panelRoute("medicines.store"),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $("#add_medicine_modal").modal("hide");

                // Keep the table on page 1 and force refresh so newly-created rows appear immediately.
                if (typeof window.Livewire !== "undefined") {
                    if (typeof window.Livewire.dispatchTo === "function") {
                        window.Livewire.dispatchTo(
                            "medicine-table",
                            "resetPage",
                        );
                        window.Livewire.dispatchTo("medicine-table", "refresh");
                    } else if (typeof window.Livewire.dispatch === "function") {
                        window.Livewire.dispatch("resetPage");
                        window.Livewire.dispatch("refresh");
                    } else {
                        window.location.reload();
                    }
                } else {
                    // Fallback for cases where Livewire global is not yet available.
                    window.location.reload();
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
        complete: function () {
            loadingBtn.prop("disabled", false);
        },
    });
});

listen("hidden.bs.modal", "#add_medicine_modal", function () {
    resetModalForm("#addMedicineForm", "#medicineCreateErrorsBox");
});
