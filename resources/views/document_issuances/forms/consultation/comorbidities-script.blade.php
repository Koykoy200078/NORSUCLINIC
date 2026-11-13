{{-- The comorbidities multi-select of the consultation form (inside its script block). Shared by the create form and the edit page (re-audit 2026-11-13, DRY). --}}
// ==================== COMORBIDITIES MULTI-SELECT ====================
const comorbiditiesInput = document.getElementById('comorbidities_input');
const comorbiditiesHiddenInput = document.getElementById('comorbidities_custom');
const comorbiditiesSelectedList = document.getElementById('comorbidities_selected_list');
const addComorbidityBtn = document.getElementById('add_comorbidity_btn');
let selectedComorbidities = [];

function normalizeComorbidity(value) {
    return String(value || '').replace(/\s+/g, ' ').trim();
}

function updateComorbiditiesHiddenInput() {
    if (comorbiditiesHiddenInput) {
        comorbiditiesHiddenInput.value = selectedComorbidities.join(', ');
    }
}

function renderComorbidityChips() {
    if (!comorbiditiesSelectedList) {
        return;
    }

    comorbiditiesSelectedList.innerHTML = '';

    selectedComorbidities.forEach((item, index) => {
        const chip = document.createElement('span');
        chip.className = 'comorbidity-chip';
        chip.textContent = item;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'comorbidity-chip-remove';
        removeBtn.setAttribute('aria-label', `Remove ${item}`);
        removeBtn.textContent = 'x';
        removeBtn.addEventListener('click', function() {
            selectedComorbidities.splice(index, 1);
            renderComorbidityChips();
            updateComorbiditiesHiddenInput();
        });

        chip.appendChild(removeBtn);
        comorbiditiesSelectedList.appendChild(chip);
    });
}

function addComorbidity(value) {
    const normalized = normalizeComorbidity(value);

    if (!normalized) {
        return;
    }

    const duplicate = selectedComorbidities.some((item) => item.toLowerCase() === normalized.toLowerCase());
    if (duplicate) {
        if (comorbiditiesInput) {
            comorbiditiesInput.value = '';
        }
        return;
    }

    selectedComorbidities.push(normalized);
    renderComorbidityChips();
    updateComorbiditiesHiddenInput();

    if (comorbiditiesInput) {
        comorbiditiesInput.value = '';
    }
}

function setComorbidities(value) {
    selectedComorbidities = [];

    const values = String(value || '')
        .split(',')
        .map((item) => normalizeComorbidity(item))
        .filter((item) => item !== '');

    values.forEach((item) => {
        const duplicate = selectedComorbidities.some((existingItem) => existingItem.toLowerCase() === item.toLowerCase());
        if (!duplicate) {
            selectedComorbidities.push(item);
        }
    });

    renderComorbidityChips();
    updateComorbiditiesHiddenInput();
}

if (addComorbidityBtn) {
    addComorbidityBtn.addEventListener('click', function() {
        addComorbidity(comorbiditiesInput ? comorbiditiesInput.value : '');
    });
}

if (comorbiditiesInput) {
    comorbiditiesInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addComorbidity(this.value);
        }
    });

    comorbiditiesInput.addEventListener('blur', function() {
        const pendingValue = normalizeComorbidity(this.value);
        if (pendingValue) {
            addComorbidity(pendingValue);
        }
    });
}

setComorbidities(comorbiditiesHiddenInput ? comorbiditiesHiddenInput.value : '');
