window.listen = function (event, selector, callback) {
    $(document).on(event, selector, callback);
};
window.listenClick = function (selector, callback) {
    $(document).on("click", selector, callback);
};
window.listenSubmit = function (selector, callback) {
    $(document).on("submit", selector, callback);
};
window.listenChange = function (selector, callback) {
    $(document).on("change", selector, callback);
};
window.listenKeyup = function (selector, callback) {
    $(document).on("keyup", selector, callback);
};
window.listenHiddenBsModal = function (selector, callback) {
    $(document).on("hidden.bs.modal", selector, callback);
};

/**
 * Resolve a route name for the current panel (admin/staff/doctors/patients).
 * Admin panel uses bare route names; other panels use the panel as a prefix.
 * @param {string} name  bare route name (e.g. 'doctor.status')
 * @param {*}      params optional route parameters
 * @returns {string} full URL
 */
window.panelRoute = function (name, params) {
    var panel = window.currentPanel || "admin";
    var routeName = panel === "admin" ? name : panel + "." + name;
    return route(routeName, params);
};
