"use strict";

// Account actions shared by the Doctors, Staff and Patients lists (re-audit 2026-11-13, DRY: each list carried its own
// copy). The lists keep their own button classes and only say whose account it is.

/**
 * Ask, then reset the account's password to the default.
 * @param {string} resetUrl the row's data-reset-url
 * @param {string} whose "doctor's", "staff's" or "patient's"
 */
window.confirmPasswordReset = function (resetUrl, whose) {
    swal({
        title: "Are you sure?",
        text: "This will reset the " + whose + " password to default (" + window.defaultPassword + ")",
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
};
