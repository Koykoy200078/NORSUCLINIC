listenClick("#doctorResetFilter", function () {
    let firstDate = moment(moment().startOf("week"), "MM/DD/YYYY")
        .day(0)
        .format("MM/DD/YYYY");
    let lastDate = moment(moment().endOf("week"), "MM/DD/YYYY")
        .day(6)
        .format("MM/DD/YYYY");

    $("#doctorPanelAppointmentDate")
        .val(firstDate + " - " + lastDate)
        .trigger("change");
    $("#doctorPanelAppointmentStatus").val(3).trigger("change");
    $("#doctorStatus").val(2).trigger("change");
    hideDropdownManually($("#doctorFilterBtn"), $(".dropdown-menu"));
});

listenChange("#doctorStatus", function () {
    Livewire.dispatch("changeStatusFilter", { value: $(this).val() });
});

listenClick(".doctor-delete-btn", function () {
    let userId = $(this).attr("data-id");
    let deleteUserUrl = $(this).attr("data-delete-url");
    deleteItem(deleteUserUrl, Lang.get("js.doctor"));
});

listenClick(".doctor-reset-password-btn", function () {
    confirmPasswordReset($(this).attr("data-reset-url"), "doctor's");
});

listenClick(".add-qualification", function () {
    let userId = $(this).attr("data-id");
    $("#qualificationID").val(userId);
    $("#qualificationModal").modal("show");
});

listenSubmit("#qualificationForm", function (e) {
    e.preventDefault();
    let $saveBtn = $("#qualificationSaveBtn");
    // Prevent double-submit
    if ($saveBtn.prop("disabled")) return;
    $saveBtn.prop("disabled", true);

    $.ajax({
        url: panelRoute("add.qualification"),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $("#year").val(null).trigger("change");
                // Hide the modal first, then clean up backdrop and trigger Livewire refresh.
                // Without the delay, Livewire re-renders the DOM while Bootstrap still
                // owns the backdrop — leaving body.modal-open and .modal-backdrop orphaned.
                $("#qualificationModal").modal("hide");
                setTimeout(function () {
                    $(".modal-backdrop").remove();
                    $("body")
                        .removeClass("modal-open")
                        .css("overflow", "")
                        .css("padding-right", "");
                    $saveBtn.prop("disabled", false);
                    Livewire.dispatch("refresh");
                }, 350);
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
            $saveBtn.prop("disabled", false);
        },
    });
});

listen("hidden.bs.modal", "#qualificationModal", function () {
    resetModalForm("#qualificationForm");
    $("#year").val(null).trigger("change");
});

listenClick(".doctor-status", function (event) {
    let doctorRecordId = $(event.currentTarget).attr("data-id");

    $.ajax({
        type: "PUT",
        url: panelRoute("doctor.status"),
        data: { id: doctorRecordId },
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                Livewire.dispatch("refresh");
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".doctor-email-verification", function (event) {
    let userId = $(event.currentTarget).attr("data-id");
    $.ajax({
        type: "POST",
        url: panelRoute("resend.email.verification", userId),
        success: function (result) {
            displaySuccessMessage(result.message);
            setTimeout(function () {
                window.location.reload();
            }, 5000);
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick("#qualificationSaveBtn", function () {
    $("#qualificationForm").trigger("submit");
});
