listenClick(".staff-delete-btn", function (event) {
    let staffRecordId = $(event.currentTarget).attr("data-id");
    let deleteUrl = $(event.currentTarget).attr("data-delete-url");
    if (!deleteUrl) return;
    deleteItem(deleteUrl, Lang.get("js.staff"));
});

listenClick(".staff-reset-password-btn", function () {
    confirmPasswordReset($(this).attr("data-reset-url"), "staff's");
});

listenChange(".staff-email-verified", function (e) {
    setEmailVerified($(e.currentTarget).attr("data-id"), $(this).is(":checked"));
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
