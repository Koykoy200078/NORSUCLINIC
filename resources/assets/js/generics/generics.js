"use strict";

listenClick(".generic-delete-btn", function (event) {
    let genericId = $(event.currentTarget).attr("data-id");
    let genericName = $(event.currentTarget).attr("data-name");
    let baseUrl = $("#indexGenericUrl").val();
    let deleteUrl = baseUrl + "/" + genericId;
    deleteItem(deleteUrl, genericName);
});

listenSubmit("#addGenericForm", function (e) {
    e.preventDefault();
    let loadingBtn = $(this).find("#genericSaveBtn");
    loadingBtn.prop("disabled", true);
    $.ajax({
        url: panelRoute("generics.store"),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $("#add_generic_modal").modal("hide");
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
});

listen("hidden.bs.modal", "#add_generic_modal", function () {
    resetModalForm("#addGenericForm", "#genericErrorsBox");
});
