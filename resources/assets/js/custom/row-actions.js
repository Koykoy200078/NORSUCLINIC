const preparedRowActions = new WeakSet();

// Window capture runs before Bootstrap's document-level handlers, including on freshly rendered Livewire rows.
function prepareRowActions(event) {
    const toggle = event.target.closest?.('[data-row-actions-toggle]');
    if (!toggle) {
        return;
    }
    const existing = bootstrap.Dropdown.getInstance(toggle);
    if (!preparedRowActions.has(toggle) || !existing) {
        existing?.dispose();
        new bootstrap.Dropdown(toggle, {
            boundary: document.body,
            popperConfig: (config) => ({ ...config, strategy: 'fixed' }),
        });
        preparedRowActions.add(toggle);
    }
}

window.addEventListener('click', prepareRowActions, true);
window.addEventListener('keydown', prepareRowActions, true);
