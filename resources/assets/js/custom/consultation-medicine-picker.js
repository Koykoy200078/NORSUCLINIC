document.addEventListener('DOMContentLoaded', initConsultationMedicinePicker);

function initConsultationMedicinePicker() {
    const element = document.getElementById('consultation_medicine_picker_config');
    if (!element) {
        return;
    }
    const config = JSON.parse(element.textContent);
    const editing = config.editing;
    const $ = window.jQuery;

    const medicinesByCategoryUrl = config.url;
    const canManagePlanMedicines = config.canManagePlanMedicines;
    let medicinesData = [];

    async function fetchMedicines() {
        try {
            const response = await fetch(medicinesByCategoryUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch medicines (${response.status})`);
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error(`Unexpected response format while fetching medicines (${response.status})`);
            }

            const result = await response.json();

            if (result.success) {
                medicinesData = result.data;

                if (editing) {
                    loadExistingMedicines();
                }
                if (addPlanMedicineBtn && canManagePlanMedicines) {
                    addPlanMedicineBtn.disabled = false;
                }
                if (addNursingMedicineBtn) {
                    addNursingMedicineBtn.disabled = false;
                }
            } else {
                throw new Error('Medicine catalog could not be loaded');
            }
        } catch (error) {
            console.error('Error fetching medicines:', error);
            const warning = document.createElement('div');
            warning.className = 'alert alert-danger';
            warning.setAttribute('role', 'alert');
            warning.textContent = 'Medicine list could not be loaded. Reload this page to try again.';
            element.insertAdjacentElement('afterend', warning);
        }
    }

    // Load existing medicines from the database
    function loadExistingMedicines() {
        const existingMedicines = (config.existingMedicines || []);

        if (existingMedicines.length === 0) {
            return;
        }

        existingMedicines.forEach(medicine => {
            const type = medicine.used_for; // 'plan' or 'nursing'
            if (type === 'plan' && !canManagePlanMedicines) {
                return;
            }

            const counter = type === 'plan' ? planMedicineCounter++ : nursingMedicineCounter++;

            // Add medicine row with existing data
            addMedicineRow(type, counter, {
                rowId: medicine.id,
                medicineId: medicine.medicine_id,
                dosage: medicine.dosage,
                quantity: medicine.quantity,
                dosageInstructions: medicine.dosage_instructions,
                isExisting: true, // Mark as existing medicine
                existingQuantity: medicine.quantity // Store the original quantity used
            });
        });
    }

    let planMedicineCounter = 0;
    let nursingMedicineCounter = 0;

    const addPlanMedicineBtn = document.getElementById('add_plan_medicine_btn');
    const addNursingMedicineBtn = document.getElementById('add_nursing_medicine_btn');
    [addPlanMedicineBtn, addNursingMedicineBtn].filter(Boolean).forEach(button => { button.disabled = true; });

    if (addPlanMedicineBtn && canManagePlanMedicines) {
        addPlanMedicineBtn.addEventListener('click', function() {
            addMedicineRow('plan', planMedicineCounter++);
        });
    }

    if (addNursingMedicineBtn) {
        addNursingMedicineBtn.addEventListener('click', function() {
            addMedicineRow('nursing', nursingMedicineCounter++);
        });
    }

    function addMedicineRow(type, index, existingData = null) {
        if (type === 'plan' && !canManagePlanMedicines) {
            return;
        }

        const container = type === 'plan' ?
            document.getElementById('plan_medicines_container') :
            document.getElementById('nursing_medicines_container');

        if (!container) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'medicine-row';
        row.dataset.type = type;
        row.dataset.index = index;

        // Add visual indicator for existing medicines
        if (existingData && existingData.isExisting) {
            row.classList.add('existing-medicine-row');
        }

        const medicineSelect = document.createElement('select');
        medicineSelect.name = `medicines[${type}][${index}][medicine_id]`;
        medicineSelect.className = 'medicine-select';
        medicineSelect.required = true;
        medicineSelect.setAttribute('aria-label', 'Medicine');

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select Medicine';
        medicineSelect.appendChild(defaultOption);

        // For existing medicines, we need to ensure the medicine appears even if out of stock
        if (existingData && existingData.isExisting) {
            // Add a special entry for the existing medicine even if it's not in medicinesData
            let medicineFound = false;

            medicinesData.forEach(category => {
                category.medicines.forEach(medicine => {
                    if (medicine.id === existingData.medicineId) {
                        medicineFound = true;
                    }
                });
            });

            // If medicine not found in available list, add it manually
            if (!medicineFound) {
                const existingMedicine = (config.existingMedicines || []).find(m => m.medicine_id === existingData.medicineId);
                if (existingMedicine && existingMedicine.medicine) {
                    const option = document.createElement('option');
                    option.value = existingMedicine.medicine.id;
                    option.textContent = `${existingMedicine.medicine.name} (Out of Stock)`;
                    option.dataset.medicineId = existingMedicine.medicine.id;
                    option.dataset.medicineName = existingMedicine.medicine.name;
                    option.dataset.dosages = JSON.stringify([{
                        dosage: existingData.dosage,
                        available_quantity: 0
                    }]);
                    option.dataset.totalStock = 0;
                    option.dataset.isOutOfStock = 'true';
                    option.selected = true;
                    medicineSelect.appendChild(option);
                }
            }
        }

        medicinesData.forEach(category => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = category.name;
            let hasValidMedicines = false;

            category.medicines.forEach(medicine => {
                const isCurrentlyUsed = existingData && medicine.id === existingData.medicineId;
                const hasStock = medicine.available_quantity > 0;
                const option = document.createElement('option');
                option.value = medicine.id;
                option.textContent = medicine.name;
                option.dataset.medicineId = medicine.id;
                option.dataset.medicineName = medicine.name;
                option.dataset.dosages = JSON.stringify(medicine.dosages);
                option.dataset.totalStock = medicine.available_quantity;
                option.dataset.isOutOfStock = hasStock ? 'false' : 'true';
                option.disabled = !hasStock && !isCurrentlyUsed;
                const strengths = [...new Set([medicine.strength, ...(medicine.dosages || []).map(item => item.dosage)].filter(Boolean))];
                option.dataset.search = [medicine.name, medicine.brand_name, medicine.generic_name, medicine.generic_label,
                    category.name, medicine.sku, ...strengths].filter(Boolean).join(' ');
                const status = hasStock ? `Stock: ${medicine.available_quantity}` :
                    (medicine.stock_status === 'expired' ? 'Expired stock — unavailable' : 'Out of stock');
                option.dataset.details = [medicine.generic_name || medicine.generic_label, strengths.join(', '), medicine.sku,
                    status, `Nearest expiry: ${medicine.nearest_expiry || 'Not recorded'}`].filter(Boolean).join(' · ');

                if (isCurrentlyUsed) {
                    option.selected = true;
                }
                optgroup.appendChild(option);
                hasValidMedicines = true;
            });

            // Only add optgroup if it has valid medicines
            if (hasValidMedicines) {
                medicineSelect.appendChild(optgroup);
            }
        });

        const dosageSelect = document.createElement('select');
        dosageSelect.name = `medicines[${type}][${index}][dosage]`;
        dosageSelect.className = 'dosage-select';
        dosageSelect.setAttribute('aria-label', 'Strength');
        dosageSelect.required = true;
        dosageSelect.disabled = true;

        const dosageDefaultOption = document.createElement('option');
        dosageDefaultOption.value = '';
        dosageDefaultOption.textContent = 'Select Dosage';
        dosageSelect.appendChild(dosageDefaultOption);

        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.setAttribute('aria-label', 'Quantity');
        quantityInput.name = `medicines[${type}][${index}][quantity]`;
        quantityInput.placeholder = 'Qty';
        quantityInput.min = '1';
        quantityInput.value = existingData ? existingData.quantity : '1';
        quantityInput.required = true;
        quantityInput.disabled = true;

        const dosageInstructions = document.createElement('input');
        dosageInstructions.type = 'text';
        dosageInstructions.name = `medicines[${type}][${index}][dosage_instructions]`;
        dosageInstructions.placeholder = 'Instructions (e.g., 1 tablet 3x a day)';
        dosageInstructions.value = existingData ? existingData.dosageInstructions || '' : '';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-medicine-btn';
        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
        removeBtn.addEventListener('click', function() {
            if (!editing) {
                $(medicineSelect).select2('destroy');
                row.remove();
                return;
            }
            const isExisting = existingData && existingData.isExisting;
            const medicineName = medicineSelect.options[medicineSelect.selectedIndex]?.textContent || 'this medicine';
            const quantity = quantityInput.value || '0';
            const dosageText = dosageSelect.options[dosageSelect.selectedIndex]?.textContent || 'N/A';

            let confirmMessage = `Remove ${medicineName}?\nDosage: ${dosageText}\nQuantity: ${quantity}`;

            if (isExisting) {
                confirmMessage += '\n\n⚠️ This will restore the stock to inventory.';
            }

            if (confirm(confirmMessage)) {
                $(medicineSelect).select2('destroy');
                row.remove();
            }
        });

        $(medicineSelect).on('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            dosageSelect.innerHTML = '';
            dosageSelect.appendChild(dosageDefaultOption.cloneNode(true));
            dosageSelect.disabled = true;
            quantityInput.disabled = true;
            if (!existingData) {
                quantityInput.value = '1';
            }

            const existingWarning = row.querySelector('.medicine-stock-info');
            if (existingWarning) {
                existingWarning.remove();
            }

            if (!selectedOption.value) return;

            const dosages = JSON.parse(selectedOption.dataset.dosages || '[]');
            const editingOriginal = existingData && existingData.isExisting && String(selectedOption.value) === String(existingData.medicineId);
            // A saved strength must remain editable even when it has no usable batch left today.
            if (editingOriginal && !dosages.some(item => item.dosage === existingData.dosage)) {
                dosages.push({dosage: existingData.dosage, available_quantity: 0});
            }

            if (dosages.length === 0) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-warning';
                warning.textContent = `⚠️ No dosages available for this medicine!`;
                row.appendChild(warning);
                return;
            }

            // Filter and populate dosages
            let hasAnyValidDosage = false;
            const isOutOfStock = selectedOption.dataset.isOutOfStock === 'true';

            dosages.forEach(dosageItem => {
                // Calculate display quantity (add existing if this is the current dosage)
                let displayQuantity = dosageItem.available_quantity;
                let isAdjusted = false;
                if (editingOriginal && dosageItem.dosage === existingData.dosage) {
                    displayQuantity = dosageItem.available_quantity + existingData.existingQuantity;
                    isAdjusted = true;
                }

                // Only show dosages with stock > 0 OR the currently used dosage (even if stock is 0)
                if (!editing || displayQuantity > 0 || (existingData && dosageItem.dosage === existingData.dosage)) {
                    const option = document.createElement('option');
                    option.value = dosageItem.dosage;

                    // Special message for out-of-stock existing medicines
                    let displayText;
                    if (isOutOfStock && existingData && dosageItem.dosage === existingData.dosage) {
                        displayText = `${dosageItem.dosage} (Stock: 0 - You used ${existingData.existingQuantity})`;
                    } else if (isAdjusted) {
                        displayText = `${dosageItem.dosage} (Stock: ${dosageItem.available_quantity} + Your ${existingData.existingQuantity} used = ${displayQuantity} available)`;
                    } else {
                        displayText = `${dosageItem.dosage} (Available: ${displayQuantity})`;
                    }

                    option.textContent = displayText;
                    option.dataset.availableQty = displayQuantity; // Use adjusted quantity for validation
                    option.dataset.originalAvailableQty = dosageItem.available_quantity; // Store original for reference
                    option.dataset.isAdjusted = isAdjusted;
                    option.dataset.isOutOfStock = isOutOfStock;

                    // Pre-select dosage if this is existing data
                    if (existingData && dosageItem.dosage === existingData.dosage) {
                        option.selected = true;
                    }

                    dosageSelect.appendChild(option);
                    hasAnyValidDosage = true;
                }
            });

            if (!hasAnyValidDosage) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-warning';
                warning.textContent = `⚠️ No dosages available with sufficient stock!`;
                row.appendChild(warning);
                return;
            }

            dosageSelect.disabled = false;

            // Trigger dosage change event if loading existing data
            if (existingData && existingData.dosage) {
                dosageSelect.dispatchEvent(new Event('change'));
            }
        });

        dosageSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            const existingWarning = row.querySelector('.medicine-stock-info');
            if (existingWarning) {
                existingWarning.remove();
            }

            if (!selectedOption.value) {
                quantityInput.disabled = true;
                quantityInput.value = '1';
                return;
            }

            const availableQty = parseInt(selectedOption.dataset.availableQty || 0);
            const isOutOfStock = selectedOption.dataset.isOutOfStock === 'true';
            const isAdjusted = selectedOption.dataset.isAdjusted === 'true';

            quantityInput.max = availableQty;
            quantityInput.disabled = false;

            // Special handling for out-of-stock existing medicines
            if (isOutOfStock && existingData && existingData.isExisting) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-error';
                warning.innerHTML = `<i class="fa fa-exclamation-triangle"></i> This medicine is completely out of stock. You can only reduce the quantity or remove it to restore ${existingData.existingQuantity} units to inventory.`;
                row.appendChild(warning);
                quantityInput.max = existingData.existingQuantity; // Can't increase, only decrease
            } else if (availableQty <= 0) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-warning';
                warning.textContent = `⚠️ This dosage is out of stock!`;
                row.appendChild(warning);
                dosageSelect.value = '';
                quantityInput.disabled = true;
            } else if (isAdjusted && availableQty < 10) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-info';
                warning.innerHTML = `<i class="fa fa-info-circle"></i> Actual Stock: ${selectedOption.dataset.originalAvailableQty} + Your ${existingData.existingQuantity} used = ${availableQty} total you can use`;
                row.appendChild(warning);
            } else if (isAdjusted) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-info';
                warning.innerHTML = `<i class="fa fa-info-circle"></i> Current Stock: ${selectedOption.dataset.originalAvailableQty} units in inventory + Your ${existingData.existingQuantity} already used`;
                row.appendChild(warning);
            } else if (availableQty < 10) {
                const warning = document.createElement('div');
                warning.className = 'medicine-stock-info medicine-stock-warning';
                warning.textContent = `⚠️ Low stock: Only ${availableQty} units available`;
                row.appendChild(warning);
            }
        });

        quantityInput.addEventListener('input', function() {
            const selectedDosageOption = dosageSelect.options[dosageSelect.selectedIndex];
            const availableQty = parseInt(selectedDosageOption.dataset.availableQty || 0);
            const isOutOfStock = selectedDosageOption.dataset.isOutOfStock === 'true';
            const quantity = parseInt(this.value || 0);

            // For out-of-stock existing medicines, only allow reducing quantity
            if (isOutOfStock && existingData && existingData.isExisting) {
                if (quantity > existingData.existingQuantity) {
                    this.value = existingData.existingQuantity;
                    alert(`This medicine is out of stock. You can only reduce from ${existingData.existingQuantity} to restore stock for other patients.`);
                }
            } else if (quantity > availableQty) {
                this.value = availableQty;
                alert(`Only ${availableQty} units available for this dosage.`);
            }
        });

        // The saved row this line edits. The server matches rows by id, so changing the medicine of
        // a saved line or removing one of two similar lines affects exactly that line.
        if (existingData && existingData.rowId) {
            row.dataset.savedRowId = existingData.rowId;
            row.dataset.originalMedicineKey = existingData.medicineId + '|' + String(existingData.dosage || '').trim().toLowerCase();
            const rowIdInput = document.createElement('input');
            rowIdInput.type = 'hidden';
            rowIdInput.name = `medicines[${type}][${index}][id]`;
            rowIdInput.value = existingData.rowId;
            row.appendChild(rowIdInput);
        }

        const medicineChoice = document.createElement('div');
        medicineChoice.className = 'consultation-medicine-choice';
        medicineChoice.appendChild(medicineSelect);
        row.appendChild(medicineChoice);
        row.appendChild(dosageSelect);
        row.appendChild(quantityInput);
        row.appendChild(dosageInstructions);

        // Add existing medicine badge if applicable
        if (existingData && existingData.isExisting) {
            const badge = document.createElement('span');
            badge.className = 'existing-medicine-badge';
            badge.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
            badge.title = 'This medicine was already saved. The available quantity shown includes the amount you already used (' + existingData.existingQuantity + ' units). Removing it will restore the stock.';
            row.appendChild(badge);
        }

        row.appendChild(removeBtn);

        container.appendChild(row);

        $(medicineSelect).select2({
            width: '100%',
            placeholder: 'Search medicines',
            matcher: matchMedicine,
            templateResult: function (item) {
                if (!item.id) {
                    return item.text;
                }
                const result = $('<span>');
                $('<span>').text(item.text).appendTo(result);
                $('<small>').addClass('d-block').text(item.element.dataset.details || 'Stock: 0 · Nearest expiry: Not recorded').appendTo(result);
                return result;
            },
        }).on('select2:open', function () {
            const search = document.querySelector('.select2-container--open .select2-search__field');
            if (search) {
                search.setAttribute('aria-label', 'Search medicines by brand, generic, strength, category or SKU');
                search.focus();
            }
        });
        $(medicineSelect).next('.select2-container').find('[role="combobox"]')
            .removeAttr('aria-labelledby').attr('aria-label', 'Medicine');

        // If loading existing data, trigger medicine select change to populate dosages
        if (existingData && existingData.medicineId) {
            medicineSelect.dispatchEvent(new Event('change'));
        }
    }

    function matchMedicine(params, item) {
        const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const terms = normalize(params.term).trim().split(/\s+/).filter(Boolean);
        if (!terms.length) {
            return item;
        }
        if (item.children) {
            const children = item.children.map(child => matchMedicine(params, child)).filter(Boolean);
            return children.length ? {...item, children} : null;
        }
        const text = normalize(item.element?.dataset.search || item.text);
        return terms.every(term => text.includes(term)) ? item : null;
    }
    function initDuplicateMedicineGuard() {
        const anchor = document.getElementById('plan_medicines_container') || document.getElementById('nursing_medicines_container');
        const form = anchor ? anchor.closest('form') : null;
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            const duplicates = [];

            ['plan_medicines_container', 'nursing_medicines_container'].forEach(function (containerId) {
                const container = document.getElementById(containerId);
                if (!container) {
                    return;
                }

                const seen = new Map();
                container.querySelectorAll('.medicine-row').forEach(function (row) {
                    const medicine = row.querySelector('.medicine-select');
                    const dosage = row.querySelector('.dosage-select');
                    if (!medicine || !medicine.value) {
                        return;
                    }

                    const key = medicine.value + '|' + String(dosage ? dosage.value : '').trim().toLowerCase();
                    seen.set(key, [...(seen.get(key) || []), row]);
                });
                seen.forEach(function (rows, key) {
                    if (rows.length < 2) {
                        return;
                    }
                    const ids = rows.map(row => row.dataset.savedRowId);
                    const retained = ids.every(Boolean) && new Set(ids).size === rows.length
                        && rows.every(row => row.dataset.originalMedicineKey === key);
                    if (!retained) {
                        const medicine = rows[0].querySelector('.medicine-select');
                        duplicates.push(medicine.options[medicine.selectedIndex].textContent);
                    }
                });
            });

            if (duplicates.length) {
                event.preventDefault();
                alert('The same medicine and strength is listed more than once: ' + duplicates.join(', ') + '. Combine the quantities into one row.');
            }
        });
    }

    initDuplicateMedicineGuard();
    fetchMedicines();
}
