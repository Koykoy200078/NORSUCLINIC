listenClick(".delete-prescription-btn", function (event) {
    let prescriptionId = $(event.currentTarget).attr("data-id");
    deleteItem(
        panelRoute("prescriptions.destroy", prescriptionId),
        Lang.get("js.prescription"),
    );
});

listenChange(".prescriptionStatus", function (event) {
    let prescriptionId = $(event.currentTarget).attr("data-id");
    prescriptionUpdateStatus(prescriptionId, event.currentTarget);
});

function prescriptionUpdateStatus(id, toggle) {
    let prescriptionStatusRoute =
        $("#prescriptionStatusRoute").val() || "prescription.status";
    $.ajax({
        url: route(prescriptionStatusRoute, id),
        method: "post",
        cache: false,
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                hideDropdownManually(
                    $("#prescriptionFilterBtn"),
                    $("#prescriptionFilter"),
                );
                // Switching a prescription off cancels it (and on returns it to the pharmacy queue), so the
                // Dispense Status column has to be redrawn.
                Livewire.dispatch("refresh");
            }
        },
        error: function (xhr) {
            // The server refused (for example the prescription was already dispensed, or it belongs to
            // another doctor): put the switch back and say why.
            if (toggle) {
                toggle.checked = !toggle.checked;
            }
            let message =
                (xhr.responseJSON && xhr.responseJSON.message) ||
                "The prescription status could not be changed.";
            displayErrorMessage(message);
        },
    });
}

listenClick("#prescriptionResetFilter", function () {
    $("#prescriptionHead").val("2").trigger("change");
    hideDropdownManually($("#prescriptionFilterBtn"), $(".dropdown-menu"));
});

listenChange("#prescriptionHead", function () {
    Livewire.dispatch("changeFilter", { value: $(this).val() });
});
