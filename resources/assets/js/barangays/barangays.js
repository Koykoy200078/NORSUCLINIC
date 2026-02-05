listenClick("#createBarangay", function () {
    $("#createBarangayModal").modal("show").appendTo("body");

    $("#cityBarangay").select2({
        dropdownParent: $("#createBarangayModal"),
    });
});

listen("hidden.bs.modal", "#createBarangayModal", function () {
    resetModalForm("#createBarangayForm", "#createBarangayValidationErrorsBox");
    $("#cityBarangay").val(null).trigger("change");
});

listen("hidden.bs.modal", "#editBarangayModal", function () {
    resetModalForm("#editBarangayForm", "#editBarangayValidationErrorsBox");
});

listenClick(".barangay-edit-btn", function (event) {
    let editBarangayId = $(event.currentTarget).attr("data-id");
    renderBarangayData(editBarangayId);

    $("#editBarangayCityId").select2({
        dropdownParent: $("#editBarangayModal"),
    });
});

function renderBarangayData(id) {
    $.ajax({
        url: route("barangays.edit", id),
        type: "GET",
        success: function (result) {
            $("#barangayID").val(result.data.id);
            $("#editBarangayName").val(result.data.name);
            $("#editBarangayCityId").val(result.data.city_id).trigger("change");
            $("#editBarangayModal").modal("show");
        },
    });
}

listenSubmit("#createBarangayForm", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("barangays.store"),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $("#createBarangayModal").modal("hide");
                Livewire.dispatch("refresh");
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenSubmit("#editBarangayForm", function (e) {
    e.preventDefault();
    let updateBarangayId = $("#barangayID").val();
    $.ajax({
        url: route("barangays.update", updateBarangayId),
        type: "PUT",
        data: $(this).serialize(),
        success: function (result) {
            $("#editBarangayModal").modal("hide");
            displaySuccessMessage(result.message);
            Livewire.dispatch("refresh");
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".barangay-delete-btn", function (event) {
    let barangayRecordId = $(event.currentTarget).attr("data-id");
    deleteItem(
        route("barangays.destroy", barangayRecordId),
        Lang.get("js.barangay"),
    );
});
