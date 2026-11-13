window.listen = function (event, selector, callback) {
    $(document).on(event, selector, callback);
};
window.listenClick = function (selector, callback) {
    $(document).on("click", selector, callback);
};
window.listenSubmit = function (selector, callback) {
    $(document).on("submit", selector, callback);
};
/**
 * After a save in a modal: sends a Livewire list its events (e.g. back to page 1, then reload its rows) so the new row
 * shows at once. Without dispatchTo it sends them to every component; without Livewire the page reloads. (Re-audit
 * 2026-11-13, DRY: the medicines and stock-in screens each had this chain.)
 * @param {string} table the list's Livewire name, e.g. "medicine-table"
 * @param {string[]} events sent in this order
 */
window.refreshLivewireTable = function (table, events) {
    if (typeof window.Livewire === "undefined") {
        window.location.reload();
    } else if (typeof window.Livewire.dispatchTo === "function") {
        events.forEach((event) => window.Livewire.dispatchTo(table, event));
    } else if (typeof window.Livewire.dispatch === "function") {
        events.forEach((event) => window.Livewire.dispatch(event));
    } else {
        window.location.reload();
    }
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
