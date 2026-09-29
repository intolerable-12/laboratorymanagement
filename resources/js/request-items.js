import * as bootstrap from 'bootstrap';

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const formatQuantity = (value, itemType) => {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return value || '—';
    }

    return itemType === 'Chemical' ? number.toFixed(2) : String(Math.trunc(number));
};

const initializeItemPicker = (root) => {
    if (root.dataset.itemPickerInitialized === 'true') {
        return;
    }

    const cart = root.querySelector('[data-item-cart]');
    const cartList = cart?.querySelector('[data-cart-list]');
    const cartEmpty = cart?.querySelector('[data-cart-empty]');
    const cartCount = cart?.querySelector('[data-cart-count]');
    const clearButton = cart?.querySelector('[data-cart-clear]');
    const modalElement = root.querySelector('[data-picker-modal]');

    if (!cart || !cartList || !modalElement) {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const quantityField = modalElement.querySelector('[data-picker-quantity]');
    const unitField = modalElement.querySelector('[data-picker-unit]');
    const unitGroup = modalElement.querySelector('[data-picker-unit-group]');
    const remarksField = modalElement.querySelector('[data-picker-remarks]');
    const error = modalElement.querySelector('[data-picker-error]');
    const itemName = modalElement.querySelector('[data-picker-selection-name]');
    const itemCode = modalElement.querySelector('[data-picker-selection-code]');
    const itemType = modalElement.querySelector('[data-picker-modal-type]');
    const availability = modalElement.querySelector('[data-picker-availability]');
    const addLabel = modalElement.querySelector('[data-picker-add-label]');
    let activeItem = null;

    root.dataset.itemPickerInitialized = 'true';

    const getEntry = (type, id) => Array.from(cartList.querySelectorAll('[data-cart-entry]'))
        .find((entry) => entry.dataset.itemType === type && entry.dataset.itemId === id);

    const syncRows = () => {
        root.querySelectorAll('[data-picker-item]').forEach((row) => {
            const isSelected = Boolean(getEntry(row.dataset.itemType, row.dataset.itemId));
            row.classList.toggle('is-selected', isSelected);

            const action = row.querySelector('[data-picker-row-action]');
            if (action) {
                action.innerHTML = isSelected
                    ? '<i class="fa-solid fa-check me-1" aria-hidden="true"></i>Added'
                    : 'Select <i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i>';
            }
        });
    };

    const syncCart = () => {
        const count = cartList.querySelectorAll('[data-cart-entry]').length;

        if (cartCount) {
            cartCount.textContent = count;
        }

        cartEmpty?.classList.toggle('d-none', count > 0);
        if (clearButton) {
            clearButton.disabled = count === 0;
        }

        syncRows();
    };

    const setError = (message = '') => {
        if (!error) {
            return;
        }

        error.textContent = message;
        error.classList.toggle('d-none', !message);
    };

    const openSelection = (row) => {
        activeItem = {
            itemType: row.dataset.itemType,
            itemId: row.dataset.itemId,
            itemName: row.dataset.itemName,
            itemCode: row.dataset.itemCode,
            itemAvailable: row.dataset.itemAvailable,
            itemUnit: row.dataset.itemUnit || '',
        };

        const existingEntry = getEntry(activeItem.itemType, activeItem.itemId);
        const existingQuantity = existingEntry?.querySelector('[data-cart-field="quantity"]')?.value;
        const existingUnit = existingEntry?.querySelector('[data-cart-field="unit"]')?.value;
        const existingRemarks = existingEntry?.querySelector('[data-cart-field="remarks"]')?.value;
        const isChemical = activeItem.itemType === 'Chemical';
        const displayUnit = isChemical ? (existingUnit || activeItem.itemUnit) : 'pcs';

        itemType.textContent = `Selected ${activeItem.itemType.toLowerCase()}`;
        itemName.textContent = activeItem.itemName;
        itemCode.textContent = activeItem.itemCode;
        availability.textContent = `${isChemical ? 'In stock' : 'Available'}: ${formatQuantity(activeItem.itemAvailable, activeItem.itemType)} ${displayUnit}`;
        quantityField.min = isChemical ? '0.01' : '1';
        quantityField.max = activeItem.itemAvailable;
        quantityField.step = isChemical ? '0.01' : '1';
        quantityField.value = existingQuantity || '';
        unitGroup?.classList.toggle('d-none', !isChemical);
        if (unitField) {
            unitField.value = existingUnit || activeItem.itemUnit || '';
        }
        remarksField.value = existingRemarks || '';
        addLabel.textContent = existingEntry ? 'Update item' : 'Add to request';
        setError();

        modal.show();
        modalElement.addEventListener('shown.bs.modal', () => quantityField.focus(), { once: true });
    };

    const createEntry = (item, quantity, unit, remarks) => {
        const entry = document.createElement('div');
        const isChemical = item.itemType === 'Chemical';
        const itemPrefix = isChemical ? 'chemical_items' : 'equipment_items';
        const displayUnit = isChemical ? unit : 'pcs';

        entry.className = 'request-cart-entry';
        entry.dataset.cartEntry = '';
        entry.dataset.itemType = item.itemType;
        entry.dataset.itemId = item.itemId;
        entry.dataset.itemAvailable = item.itemAvailable;
        entry.dataset.itemUnit = displayUnit;
        entry.innerHTML = `
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="min-width-0">
                    <div class="small text-uppercase text-secondary">${escapeHtml(item.itemType)}</div>
                    <div class="fw-semibold text-dark text-truncate" data-cart-item-name>${escapeHtml(item.itemName)}</div>
                    <div class="small text-secondary">${escapeHtml(item.itemCode)}</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" data-cart-remove aria-label="Remove ${escapeHtml(item.itemName)}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <span class="badge rounded-pill text-bg-primary" data-cart-summary>${escapeHtml(formatQuantity(quantity, item.itemType))} ${escapeHtml(displayUnit)}</span>
                <span class="small text-secondary">Available: ${escapeHtml(formatQuantity(item.itemAvailable, item.itemType))} ${escapeHtml(displayUnit)}</span>
            </div>
            <input type="hidden" name="${itemPrefix}[${item.itemId}][quantity]" value="${escapeHtml(quantity)}" data-cart-field="quantity">
            ${isChemical ? `<input type="hidden" name="chemical_items[${item.itemId}][unit]" value="${escapeHtml(unit)}" data-cart-field="unit">` : ''}
            <input type="hidden" name="${itemPrefix}[${item.itemId}][remarks]" value="${escapeHtml(remarks)}" data-cart-field="remarks">
        `;

        return entry;
    };

    const updateEntry = (entry, quantity, unit, remarks, type) => {
        entry.querySelector('[data-cart-field="quantity"]').value = quantity;
        entry.querySelector('[data-cart-field="remarks"]').value = remarks;
        entry.querySelector('[data-cart-summary]').textContent = `${formatQuantity(quantity, type)} ${type === 'Chemical' ? unit : 'pcs'}`;

        const unitInput = entry.querySelector('[data-cart-field="unit"]');
        if (unitInput) {
            unitInput.value = unit;
        }
        entry.dataset.itemUnit = type === 'Chemical' ? unit : 'pcs';
    };

    const closeModal = () => {
        modal.hide();
        activeItem = null;
    };

    root.addEventListener('click', (event) => {
        const row = event.target.closest('[data-picker-item]');

        if (row && root.contains(row)) {
            event.preventDefault();
            openSelection(row);
            return;
        }

        const addButton = event.target.closest('[data-picker-add]');
        if (addButton && root.contains(addButton)) {
            if (!activeItem) {
                return;
            }

            const quantity = Number(quantityField.value);
            const available = Number(activeItem.itemAvailable);
            const isChemical = activeItem.itemType === 'Chemical';
            const unit = unitField?.value.trim() || activeItem.itemUnit || '';
            const remarks = remarksField.value.trim();

            if (!Number.isFinite(quantity) || quantity <= 0 || quantity > available || (!isChemical && !Number.isInteger(quantity)) || (isChemical && unit === '')) {
                setError(quantity > available
                    ? `Quantity cannot exceed ${activeItem.itemAvailable || 'the available amount'}.`
                    : (isChemical && unit === '' ? 'Enter the unit for this chemical.' : 'Enter a valid quantity.'));
                quantityField.focus();
                return;
            }

            const existingEntry = getEntry(activeItem.itemType, activeItem.itemId);
            if (existingEntry) {
                updateEntry(existingEntry, quantity, unit, remarks, activeItem.itemType);
            } else {
                cartList.append(createEntry(activeItem, quantity, unit, remarks));
            }

            syncCart();
            closeModal();
            return;
        }

        const removeButton = event.target.closest('[data-cart-remove]');
        if (removeButton && root.contains(removeButton)) {
            removeButton.closest('[data-cart-entry]')?.remove();
            syncCart();
            return;
        }

        const clearButtonTarget = event.target.closest('[data-cart-clear]');
        if (clearButtonTarget && root.contains(clearButtonTarget)) {
            cartList.replaceChildren();
            syncCart();
        }
    });

    root.addEventListener('request-items-content-replaced', () => {
        closeModal();
        syncCart();
    });

    root.addEventListener('request-items-laboratory-changed', () => {
        closeModal();
        cartList.replaceChildren();
        syncCart();
    });

    root.addEventListener('keydown', (event) => {
        const row = event.target.closest('[data-picker-item]');

        if (!row || !root.contains(row) || !['Enter', ' '].includes(event.key)) {
            return;
        }

        event.preventDefault();
        openSelection(row);
    });

    syncCart();
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-item-picker]').forEach(initializeItemPicker);
});
