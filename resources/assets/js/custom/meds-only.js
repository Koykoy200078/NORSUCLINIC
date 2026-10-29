// Meds Only form (patients who only come for medicine): the registered-patient search, and one or many drug rows.
// The server decides everything that matters (stock, duplicates, validation); this only makes the form easy to fill.
document.addEventListener('DOMContentLoaded', initMedsOnlyForm);
document.addEventListener('turbo:load', initMedsOnlyForm);

function initMedsOnlyForm() {
    const form = document.getElementById('meds-only-form');
    const configElement = document.getElementById('meds_only_config');
    if (!form || !configElement || form.dataset.medsOnlyReady === '1') {
        return;
    }
    form.dataset.medsOnlyReady = '1';

    const $ = window.jQuery;
    const config = JSON.parse(configElement.textContent);
    const rows = document.getElementById('medicine-rows');
    const errorBox = document.getElementById('medicines-error');
    let medicines = [];
    let nextIndex = 0;

    // ------------------------------------------------------------ the registered patient

    const $patient = $('#patient_id');
    const typedBox = document.getElementById('typed-name-box');
    const typedInput = document.getElementById('patient_name');

    function syncTypedName() {
        const registered = !!$patient.val();
        typedBox.classList.toggle('d-none', registered);
        typedInput.required = !registered;
    }

    function fill(id, value) {
        const element = document.getElementById(id);
        if (element && value) {
            element.value = String(value);
        }
    }

    $patient.select2({
        width: '100%',
        allowClear: true,
        minimumInputLength: 2,
        placeholder: $patient.data('placeholder') || '',
        ajax: {
            url: config.patientsUrl,
            dataType: 'json',
            delay: 250,
            data: (params) => ({ term: params.term }),
            processResults: (data) => ({ results: data.results }),
        },
    });
    if (config.patient) {
        $patient.append(new Option(config.patient.text, config.patient.id, true, true)).trigger('change');
    }
    $patient.on('select2:select', (event) => {
        const patient = event.params.data;
        fill('college_id', patient.college_id);
        fill('course_id', patient.course_id);
        fill('year_level_id', patient.year_level_id);
        fill('campus_id', patient.campus_id);
        fill('patient_type_id', patient.patient_type_id);
        syncTypedName();
    });
    $patient.on('change select2:clear', syncTypedName);
    syncTypedName();

    // ------------------------------------------------------------ the drug rows

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.toggle('d-none', !message);
    }

    function matcher(params, data) {
        const term = (params.term || '').trim().toLowerCase();
        if (!term) {
            return data;
        }
        if (data.children) {
            const children = data.children.map((child) => matcher(params, child)).filter(Boolean);
            return children.length ? Object.assign({}, data, { children }) : null;
        }
        const haystack = (data.text + ' ' + ($(data.element).attr('data-search') || '')).toLowerCase();

        return term.split(/\s+/).every((word) => haystack.includes(word)) ? data : null;
    }

    function medicineById(id) {
        return medicines.find((medicine) => String(medicine.id) === String(id));
    }

    function fillMedicineOptions(select, selectedId) {
        const groups = {};
        medicines.forEach((medicine) => {
            const option = document.createElement('option');
            option.value = medicine.id;
            const strength = medicine.strength ? ' · ' + medicine.strength : '';
            option.textContent = medicine.name + strength + (medicine.disabled ? ' (no stock to give)' : '');
            option.disabled = !!medicine.disabled && String(medicine.id) !== String(selectedId);
            option.dataset.search = [medicine.generic_name, medicine.generic_label, medicine.brand_name, medicine.category, medicine.strength, medicine.sku].filter(Boolean).join(' ');
            (groups[medicine.category] = groups[medicine.category] || []).push(option);
        });
        select.replaceChildren(new Option('', ''));
        Object.keys(groups).forEach((category) => {
            const group = document.createElement('optgroup');
            group.label = category;
            groups[category].forEach((option) => group.appendChild(option));
            select.appendChild(group);
        });
    }

    function fillDosages(row, preselected) {
        const medicine = medicineById(row.querySelector('.med-select').value);
        const dosage = row.querySelector('.med-dosage');
        const quantity = row.querySelector('.med-quantity');
        const stock = row.querySelector('.med-stock');
        dosage.replaceChildren(new Option('Any strength (nearest expiry)', ''));
        quantity.removeAttribute('max');
        stock.textContent = '';
        if (!medicine) {
            return;
        }
        (medicine.dosages || []).forEach((entry) => {
            const option = document.createElement('option');
            option.value = entry.dosage === 'N/A' ? '' : entry.dosage;
            option.textContent = entry.dosage + ' (' + entry.available_quantity + ' available)';
            if (option.value !== '') {
                dosage.appendChild(option);
            }
        });
        if (preselected && !Array.from(dosage.options).some((option) => option.value === preselected)) {
            const option = document.createElement('option');
            option.value = preselected;
            option.textContent = preselected + ' (not in stock now)';
            dosage.appendChild(option);
        }
        if (preselected) {
            dosage.value = preselected;
        }
        updateLimit(row);
    }

    function updateLimit(row) {
        const medicine = medicineById(row.querySelector('.med-select').value);
        const dosage = row.querySelector('.med-dosage').value;
        const quantity = row.querySelector('.med-quantity');
        const stock = row.querySelector('.med-stock');
        if (!medicine) {
            return;
        }
        const match = (medicine.dosages || []).find((entry) => entry.dosage === dosage);
        const available = dosage === '' ? Number(medicine.available_quantity || 0) : Number(match ? match.available_quantity : 0);
        stock.textContent = available + ' in stock';
        if (available > 0) {
            quantity.max = available;
        } else {
            quantity.removeAttribute('max');
        }
    }

    function addRow(line) {
        const index = nextIndex++;
        // The markup is a <template> in the page; only the row number is filled in.
        const row = document.getElementById('medicine-row-template').content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[name]').forEach((element) => {
            element.name = element.name.replace('__INDEX__', String(index));
        });
        rows.appendChild(row);

        const select = row.querySelector('.med-select');
        fillMedicineOptions(select, line ? line.medicine_id : null);
        $(select).select2({ width: '100%', placeholder: 'Search medicine, generic, brand...', allowClear: false, matcher });
        if (line) {
            select.value = String(line.medicine_id);
            $(select).trigger('change.select2');
            fillDosages(row, line.dosage || '');
            row.querySelector('.med-quantity').value = line.quantity;
        } else {
            fillDosages(row, '');
        }

        $(select).on('change', () => {
            showError('');
            fillDosages(row, '');
        });
        row.querySelector('.med-dosage').addEventListener('change', () => updateLimit(row));
        row.querySelector('.remove-row').addEventListener('click', () => {
            $(select).select2('destroy');
            row.remove();
            if (!rows.children.length) {
                addRow(null);
            }
        });
    }

    document.getElementById('add-medicine-row').addEventListener('click', () => addRow(null));

    form.addEventListener('submit', (event) => {
        const seen = new Set();
        for (const row of rows.querySelectorAll('.meds-row')) {
            const key = row.querySelector('.med-select').value + '|' + row.querySelector('.med-dosage').value.toLowerCase();
            if (row.querySelector('.med-select').value !== '' && seen.has(key)) {
                event.preventDefault();
                showError('The same drug and strength is listed twice. Combine the quantities into one row.');

                return;
            }
            seen.add(key);
        }
    });

    fetch(config.medicinesUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then((response) => (response.ok ? response.json() : Promise.reject(new Error('HTTP ' + response.status))))
        .then((result) => {
            if (!result.success) {
                throw new Error('The list could not be loaded');
            }
            result.data.forEach((category) => (category.medicines || []).forEach((medicine) => medicines.push(Object.assign({ category: category.name }, medicine))));
            const lines = config.lines && config.lines.length ? config.lines : [null];
            lines.forEach((line) => addRow(line));
        })
        .catch(() => showError('The medicine list could not be loaded. Reload this page to try again.'));
}
