listenClick(".staff-delete-btn", function (event) {
    let staffRecordId = $(event.currentTarget).attr("data-id");
    let deleteUrl = $(event.currentTarget).attr("data-delete-url");
    if (!deleteUrl) return;
    deleteItem(deleteUrl, Lang.get("js.staff"));
});

listenClick(".staff-reset-password-btn", function () {
    let userId = $(this).attr("data-id");
    let resetUrl = $(this).attr("data-reset-url");
    swal({
        title: "Are you sure?",
        text: "This will reset the staff's password to default (123456)",
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancel",
                value: false,
                visible: true,
                className: "btn btn-secondary",
                closeModal: true,
            },
            confirm: {
                text: "Yes, reset it!",
                value: true,
                visible: true,
                className: "btn btn-warning",
                closeModal: true,
            },
        },
        dangerMode: true,
    }).then(function (willReset) {
        if (willReset) {
            $.ajax({
                type: "POST",
                url: resetUrl,
                success: function (result) {
                    if (result.success) {
                        displaySuccessMessage(result.message);
                    } else {
                        displayErrorMessage(
                            result.message || "Failed to reset password",
                        );
                    }
                },
                error: function (result) {
                    displayErrorMessage(
                        (result.responseJSON && result.responseJSON.message) ||
                            "Failed to reset password",
                    );
                },
            });
        }
    });
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
