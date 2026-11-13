{{-- The test picker of the lab request create and edit pages: the "N selected" badge, the Others rows (custom tests) and
     Select all / Clear per category. Included inside each page's script (re-audit 2026-11-13, DRY: the edit page had an
     older copy; this is the create page's, which also refills a custom test after a refused save). --}}
const badge = document.getElementById('selected-count-badge');
const customTestsContainer = document.getElementById('custom-tests-container');
const addCustomTestBtn = document.getElementById('add-custom-test-btn');

function getCustomTestCount() {
    if (!customTestsContainer) {
        return 0;
    }

    return Array.from(customTestsContainer.querySelectorAll('.custom-test-input'))
        .filter((input) => input.value.trim() !== '')
        .length;
}

function bindCustomTestRowEvents(row) {
    const input = row.querySelector('.custom-test-input');
    const removeButton = row.querySelector('.remove-custom-test-btn');

    if (input) {
        input.addEventListener('input', updateBadge);
    }

    if (removeButton) {
        removeButton.addEventListener('click', function() {
            row.remove();
            updateBadge();
        });
    }
}

function appendCustomTestRow(value = '') {
    if (!customTestsContainer) {
        return;
    }

    const row = document.createElement('div');
    row.className = 'input-group mb-2 custom-test-row';
    row.innerHTML = `
        <input type="text" name="custom_tests[]" class="form-control custom-test-input" placeholder="Enter custom test name" value="${String(value).replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]))}">
        <button type="button" class="btn btn-outline-danger remove-custom-test-btn" title="Remove this custom test">
            <i class="fas fa-times"></i>
        </button>
    `;

    customTestsContainer.appendChild(row);
    bindCustomTestRowEvents(row);
    updateBadge();
}

function updateBadge() {
    const selectedCheckboxCount = document.querySelectorAll('.test-checkbox:checked').length;
    const customTestCount = getCustomTestCount();
    const count = selectedCheckboxCount + customTestCount;

    badge.textContent = count + ' selected';
    badge.classList.toggle('bg-white', count === 0);
    badge.classList.toggle('text-primary', count === 0);
    badge.classList.toggle('bg-success', count > 0);
    badge.classList.toggle('text-white', count > 0);
}

document.querySelectorAll('.test-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBadge);
});

if (customTestsContainer) {
    customTestsContainer.querySelectorAll('.custom-test-row').forEach((row) => {
        bindCustomTestRowEvents(row);
    });
}

if (addCustomTestBtn) {
    addCustomTestBtn.addEventListener('click', function() {
        appendCustomTestRow('');
    });
}

// Select All / Clear per category
document.querySelectorAll('.select-all-category').forEach(btn => {
    btn.addEventListener('click', () => {
        const cat = btn.dataset.category;
        document.querySelectorAll(`.lab-test-item[data-category="${cat}"] .test-checkbox`)
            .forEach(cb => {
                cb.checked = true;
            });
        updateBadge();
    });
});

document.querySelectorAll('.deselect-all-category').forEach(btn => {
    btn.addEventListener('click', () => {
        const cat = btn.dataset.category;
        document.querySelectorAll(`.lab-test-item[data-category="${cat}"] .test-checkbox`)
            .forEach(cb => {
                cb.checked = false;
            });
        updateBadge();
    });
});

// Initial count on page load (for old() re-population)
updateBadge();
