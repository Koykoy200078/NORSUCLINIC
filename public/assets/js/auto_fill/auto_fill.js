"use strict";

$(document).ready(function () {
    $(".admin-login").click();
});

window.changeCredentials = function (email, password) {
    $("#email").val(email);
    $("#password").val(password);
};

$(document).on("click", ".admin-login", function () {
    changeCredentials("admin@norsuclinic.com", "123456");
});

$(document).on("click", ".doctor-login", function () {
    changeCredentials("doctor@norsuclinic.com", "123456");
});
