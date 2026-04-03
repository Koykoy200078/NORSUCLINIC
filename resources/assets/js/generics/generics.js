"use strict";

listenClick(".generic-delete-btn", function (event) {
    let genericId = $(event.currentTarget).attr("data-id");
    let genericName = $(event.currentTarget).attr("data-name");
    let baseUrl = $("#indexGenericUrl").val();
    let deleteUrl = baseUrl + "/" + genericId;
    deleteItem(deleteUrl, genericName);
});
