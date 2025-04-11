"use strict";

$(document).ready(function () {
    $(".admin-login").click();
});

window.changeCredentials = function (email, password) {
    $("#email").val(email);
    $("#password").val(password);
};
