// document.addEventListener('DOMContentLoaded', loadAppointmentFilterDate)

let patientFilterDate = "#patientDateFilter";
var patientStart = moment().subtract(100, "years");
var patientEnd = moment();

Livewire.hook("element.init", () => {
    loadAppointmentFilterDate();
    if (patientStart != undefined && patientEnd != undefined) {
        cb(patientStart, patientEnd);
    }
});

function loadAppointmentFilterDate() {
    if (!$(patientFilterDate).length) {
        return;
    }

    let timeRange = $("#patientDateFilter");
    // let patientStart = moment().subtract(100, "years");
    // let patientEnd = moment();

    timeRange.daterangepicker(
        {
            startDate: patientStart,
            endDate: patientEnd,
            opens: "left",
            showDropdowns: true,
            locale: {
                customRangeLabel: Lang.get("js.custom"),
                applyLabel: Lang.get("js.apply"),
                cancelLabel: Lang.get("js.cancel"),
                fromLabel: Lang.get("js.from"),
                toLabel: Lang.get("js.to"),
                monthNames: [
                    Lang.get("js.jan"),
                    Lang.get("js.feb"),
                    Lang.get("js.mar"),
                    Lang.get("js.apr"),
                    Lang.get("js.may"),
                    Lang.get("js.jun"),
                    Lang.get("js.jul"),
                    Lang.get("js.aug"),
                    Lang.get("js.sep"),
                    Lang.get("js.oct"),
                    Lang.get("js.nov"),
                    Lang.get("js.dec"),
                ],

                daysOfWeek: [
                    Lang.get("js.sun"),
                    Lang.get("js.mon"),
                    Lang.get("js.tue"),
                    Lang.get("js.wed"),
                    Lang.get("js.thu"),
                    Lang.get("js.fri"),
                    Lang.get("js.sat"),
                ],
            },
            ranges: {
                [Lang.get("js.all")]: [
                    moment().subtract(100, "years"),
                    moment(),
                ],
                [Lang.get("js.today")]: [moment(), moment()],
                [Lang.get("js.yesterday")]: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                [Lang.get("js.this_week")]: [
                    moment().startOf("week"),
                    moment().endOf("week"),
                ],
                [Lang.get("js.last_30_days")]: [
                    moment().subtract(29, "days"),
                    moment(),
                ],
                [Lang.get("js.this_month")]: [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
                [Lang.get("js.last_month")]: [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
        },
        //  cb
    );

    // cb(patientStart, patientEnd)

    timeRange.on("apply.daterangepicker", function (ev, picker) {
        let date =
            picker.startDate.format("DD/MM/YYYY") +
            " - " +
            picker.endDate.format("DD/MM/YYYY");
        Livewire.dispatch("changeDateFilter", { date: date });

        patientStart = picker.startDate;
        patientEnd = picker.endDate;
    });
}

function cb(start, end) {
    $("#patientDateFilter").val(
        start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"),
    );
}

// Enhanced patient archive function
window.archivePatientWithCascade = function (url, patientName, patientId) {
    swal({
        title: "Archive Patient?",
        text: `Are you sure you want to archive ${patientName}? The patient and their records will be hidden from the active list.`,
        icon: "warning",
        buttons: {
            cancel: "Cancel",
            confirm: {
                text: "Yes, Archive Patient",
                className: "btn btn-danger",
            },
        },
        dangerMode: true,
    }).then(function (willArchive) {
        if (willArchive) {
            $.ajax({
                url: url,
                type: "DELETE",
                dataType: "json",
                success: function (obj) {
                    if (obj.success) {
                        Livewire.dispatch("refresh");
                        Livewire.dispatch("resetPage");
                    }

                    swal({
                        icon: "success",
                        title: "Archived!",
                        text: obj.message || "Patient archived successfully.",
                        timer: 2200,
                        buttons: {
                            confirm: Lang.get("js.ok"),
                        },
                    });
                },
                error: function (data) {
                    swal({
                        title: Lang.get("js.error"),
                        icon: "error",
                        text:
                            data.responseJSON?.message ||
                            "Failed to archive patient.",
                        type: "error",
                        timer: 4000,
                        buttons: {
                            confirm: Lang.get("js.ok"),
                        },
                    });
                },
            });
        }
    });
};

listenClick(".patient-delete-btn", function () {
    let patientId = $(this).attr("data-id");
    let deleteUrl = $(this).attr("data-delete-url");
    let patientName = $(this).attr("data-patient-name") || "Patient";

    let url = deleteUrl || panelRoute("patients.destroy", patientId);

    archivePatientWithCascade(url, patientName, patientId);
});

listenClick(".patient-restore-btn", function () {
    let patientId = $(this).attr("data-id");
    let restoreUrl = $(this).attr("data-restore-url");
    let patientName = $(this).attr("data-patient-name") || "Patient";

    swal({
        title: "Restore Patient",
        text: `Are you sure you want to restore ${patientName}?`,
        icon: "info",
        buttons: {
            cancel: "Cancel",
            confirm: {
                text: "Yes, Restore Patient",
                className: "btn btn-success",
            },
        },
    }).then(function (willRestore) {
        if (willRestore) {
            $.ajax({
                type: "POST",
                url: restoreUrl,
                success: function (result) {
                    displaySuccessMessage(result.message);
                    Livewire.dispatch("refresh");
                },
                error: function (result) {
                    displayErrorMessage(result.responseJSON.message);
                },
            });
        }
    });
});

listenClick(".patient-reset-password-btn", function () {
    let userId = $(this).attr("data-id");
    let resetUrl = $(this).attr("data-reset-url");

    swal({
        title: "Are you sure?",
        text: "This will reset the patient's password to default (123456)",
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

listenChange(".patient-email-verified", function (e) {
    let patientRecordId = $(e.currentTarget).attr("data-id");
    let value = $(this).is(":checked") ? 1 : 0;
    $.ajax({
        type: "POST",
        url: panelRoute("emailVerified"),
        data: {
            id: patientRecordId,
            value: value,
        },
        success: function (result) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(result.message);
        },
    });
});

listenClick(".patient-email-verification", function (event) {
    let userId = $(event.currentTarget).attr("data-id");
    let verificationUrl = $(event.currentTarget).attr("data-verification-url");

    $.ajax({
        type: "POST",
        url: verificationUrl || panelRoute("resend.email.verification", userId), // Fallback to panel route
        success: function (result) {
            displaySuccessMessage(result.message);
            setTimeout(function () {
                window.location.reload();
                // Turbo.visit(window.location.href);
            }, 5000);
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});
