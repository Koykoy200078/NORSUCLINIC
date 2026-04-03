listenClick(".staff-delete-btn", function (event) {
    let staffRecordId = $(event.currentTarget).attr("data-id");
    let deleteUrl = $(event.currentTarget).attr("data-delete-url");
    if (!deleteUrl) return;
    deleteItem(deleteUrl, Lang.get("js.staff"));
});

listenChange(".staff-email-verified", function (e) {
    let verifyRecordId = $(e.currentTarget).attr("data-id");
    let value = $(this).is(":checked") ? 1 : 0;
    $.ajax({
        type: "POST",
        url: panelRoute("emailVerified"),
        data: {
            id: verifyRecordId,
            value: value,
        },
        success: function (result) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(result.message);
        },
    });
});

listenClick(".staff-email-verification", function (event) {
    let staffVerifyId = $(event.currentTarget).attr("data-id");
    $.ajax({
        type: "POST",
        url: panelRoute("resend.email.verification", staffVerifyId),
        success: function (result) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(result.message);
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});
