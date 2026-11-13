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

/**
 * The "email verified" switch of a list row.
 * @param {string} recordId the row's data-id
 * @param {boolean} verified whether the switch is now on
 */
window.setEmailVerified = function (recordId, verified) {
    $.ajax({
        type: "POST",
        url: panelRoute("emailVerified"),
        data: {
            id: recordId,
            value: verified ? 1 : 0,
        },
        success: function (result) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(result.message);
        },
    });
};

// The doctor, staff and patient forms: without an uploaded picture, the avatar follows the chosen gender. (It was in each
// form's create-edit.js; the three run in the same bundle, so every change ran it three times.)
listenChange("input[type=radio][name=gender]", function () {
    let file = $("#profilePicture").val();
    if (isEmpty(file)) {
        if (this.value == 1) {
            $(".image-input-wrapper").attr(
                "style",
                "background-image:url(" + manAvatar + ")",
            );
        } else if (this.value == 2) {
            $(".image-input-wrapper").attr(
                "style",
                "background-image:url(" + womanAvatar + ")",
            );
        }
    }
});
