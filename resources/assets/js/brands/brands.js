"use strict";

listenClick(".brand-delete-btn", function (event) {
    let brandId = $(event.currentTarget).attr("data-id");
    let deleteUrl = $(event.currentTarget).attr("data-delete-url");
    if (!deleteUrl) return;
    deleteItem(deleteUrl, Lang.get("js.brand"));
});

listenSubmit("#createBrandForm, #editBrandForm", function () {
    if ($("#error-msg").text() !== "") {
        $("#phoneNumber").focus();
        return false;
    }
});
