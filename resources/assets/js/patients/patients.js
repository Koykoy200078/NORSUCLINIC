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

listenClick(".patient-delete-btn", function () {
    let patientId = $(this).attr("data-id");
    let deleteUrl = $(this).attr("data-delete-url");
    let patientName = $(this).attr("data-patient-name") || "Patient";

    // Use the role-based delete URL from the button's data attribute
    // Falls back to panel-aware route if not specified
    let url = deleteUrl || panelRoute("patients.destroy", patientId);

    // Enhanced patient deletion with cascade information
    deletePatientWithCascade(url, patientName, patientId);
});

// Enhanced patient deletion function with cascade information
window.deletePatientWithCascade = function (url, patientName, patientId) {
    // Show enhanced confirmation dialog with cascade information
    swal({
        title: "Delete Patient - Complete Data Removal",
        content: {
            element: "div",
            attributes: {
                innerHTML: `
                    <div style="text-align: left; margin: 20px 0;">
                        <p><strong>Patient:</strong> ${patientName}</p>
                        <br>
                        <p><strong>⚠️ Warning:</strong> This will permanently delete the patient and ALL related data including:</p>
                        <ul style="margin: 15px 0; padding-left: 20px;">
                            <li>📅 All appointments and scheduling records</li>
                            <li>🏥 All queue entries and visit history</li>
                            <li>💊 All prescriptions and medicine records</li>
                            <li>🧾 All medicine bills and payment records</li>
                            <li>📄 All request documents and files</li>
                            <li>📱 User account and login credentials</li>
                            <li>🏠 Address and contact information</li>
                            <li>📁 All uploaded media files</li>
                            <li>📋 All activity logs and audit trails</li>
                        </ul>
                        <p style="color: #d32f2f; font-weight: bold;">This action cannot be undone!</p>
                    </div>
                `,
            },
        },
        buttons: {
            cancel: {
                text: "Cancel",
                value: false,
                visible: true,
                className: "btn btn-secondary",
                closeModal: true,
            },
            confirm: {
                text: "Yes, Delete All Data",
                value: true,
                visible: true,
                className: "btn btn-danger",
                closeModal: true,
            },
        },
        dangerMode: true,
        icon: "warning",
    }).then(function (willDelete) {
        if (willDelete) {
            // Show loading state
            swal({
                title: "Deleting Patient Data...",
                text: "Please wait while we remove all patient records.",
                icon: "info",
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
            });

            // Execute the deletion
            // Call the deleteItemAjax function from custom.js
            window.deleteItemAjax(url, patientName, null);
        }
    });
};

listenClick(".patient-reset-password-btn", function () {
    let userId = $(this).attr("data-id");
    let resetUrl = $(this).attr("data-reset-url");

    Swal.fire({
        title: "Are you sure?",
        text: "This will reset the patient's password to default (123456)",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, reset it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: resetUrl,
                success: function (result) {
                    if (result.success) {
                        displaySuccessMessage(result.message);
                    }
                },
                error: function (result) {
                    displayErrorMessage(
                        result.responseJSON.message ||
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
