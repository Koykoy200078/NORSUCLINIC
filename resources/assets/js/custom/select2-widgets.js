function initializeSelect2(root = document) {
    const controls = $(root).find('[data-control="select2"]')
        .add($(root).filter('[data-control="select2"]'));

    controls.each(function () {
        const control = $(this);
        const instance = control.data('select2');

        if (instance && instance.$container[0].isConnected) {
            return;
        }

        // A morph can keep the select while replacing its generated container.
        if (instance) {
            control.select2('destroy');
        }
        control.select2();
    });
}

document.addEventListener('DOMContentLoaded', () => initializeSelect2());
document.addEventListener('livewire:init', () => {
    Livewire.hook('morphed', ({el}) => initializeSelect2(el));
});
