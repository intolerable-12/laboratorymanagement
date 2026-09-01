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

    if (!cart || !cartList) {
        return;
    }

    root.dataset.itemPickerInitialized = 'true';

    const getEntry = (itemType, itemId) => Array.from(cartList.querySelectorAll('[data-cart-entry]'))
        .find((entry) => entry.dataset.itemType === itemType && entry.dataset.itemId === itemId);

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

    const hideSelections = () => {
        root.querySelectorAll('[data-picker-selection]').forEach((panel) => {
            panel.classList.add('d-none');
            panel.removeAttribute('data-item-id');
            panel.removeAttribute('data-item-type');
            panel.removeAttribute('data-item-name');
            panel.removeAttribute('data-item-code');
            panel.removeAttribute('data-item-available');
            panel.removeAttribute('data-item-unit');
            panel.querySelector('[data-picker-error]')?.classList.add('d-none');
        });
        root.querySelectorAll('[data-picker-item].is-picking').forEach((row) => row.classList.remove('is-picking'));
    };

    const openSelection = (row) => {
        const panel = row.closest('[data-reservation-tab-pane]')?.querySelector('[data-picker-selection]');

        if (!panel) {
            return;
        }

        hideSelections();

        panel.dataset.itemId = row.dataset.itemId;
        panel.dataset.itemType = row.dataset.itemType;
        panel.dataset.itemName = row.dataset.itemName;
        panel.dataset.itemCode = row.dataset.itemCode;
        panel.dataset.itemAvailable = row.dataset.itemAvailable;
        panel.dataset.itemUnit = row.dataset.itemUnit || '';
        panel.classList.remove('d-none');
        row.classList.add('is-picking');

        const existingEntry = getEntry(row.dataset.itemType, row.dataset.itemId);
        const quantityField = panel.querySelector('[data-picker-quantity]');
        const unitField = panel.querySelector('[data-picker-unit]');
        const remarksField = panel.querySelector('[data-picker-remarks]');
        const existingQuantity = existingEntry?.querySelector('[data-cart-field="quantity"]')?.value;
        const existingUnit = existingEntry?.querySelector('[data-cart-field="unit"]')?.value;
        const existingRemarks = existingEntry?.querySelector('[data-cart-field="remarks"]')?.value;

        panel.querySelector('[data-picker-selection-name]').textContent = `${row.dataset.itemName} (${row.dataset.itemCode})`;
        quantityField.min = row.dataset.itemType === 'Chemical' ? '0.01' : '1';
        quantityField.max = row.dataset.itemAvailable;
        quantityField.step = row.dataset.itemType === 'Chemical' ? '0.01' : '1';
        quantityField.value = existingQuantity || '';
        if (unitField) {
            unitField.value = existingUnit || row.dataset.itemUnit || '';
        }
        if (remarksField) {
            remarksField.value = existingRemarks || '';
        }

        window.setTimeout(() => quantityField.focus(), 0);
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

    const updateEntry = (entry, quantity, unit, remarks, itemType) => {
        entry.querySelector('[data-cart-field="quantity"]').value = quantity;
        entry.querySelector('[data-cart-field="remarks"]').value = remarks;
        entry.querySelector('[data-cart-summary]').textContent = `${formatQuantity(quantity, itemType)} ${itemType === 'Chemical' ? unit : 'pcs'}`;

        const unitField = entry.querySelector('[data-cart-field="unit"]');
        if (unitField) {
            unitField.value = unit;
        }
        entry.dataset.itemUnit = itemType === 'Chemical' ? unit : 'pcs';
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
            const panel = addButton.closest('[data-picker-selection]');
            const quantityField = panel?.querySelector('[data-picker-quantity]');
            const error = panel?.querySelector('[data-picker-error]');
            const item = panel ? {
                itemType: panel.dataset.itemType,
                itemId: panel.dataset.itemId,
                itemName: panel.dataset.itemName,
                itemCode: panel.dataset.itemCode,
                itemAvailable: panel.dataset.itemAvailable,
                itemUnit: panel.dataset.itemUnit || '',
            } : null;
            const quantity = Number(quantityField?.value);
            const available = Number(item?.itemAvailable);
            const isChemical = item?.itemType === 'Chemical';
            const unitField = panel?.querySelector('[data-picker-unit]');
            const remarksField = panel?.querySelector('[data-picker-remarks]');
            const unit = unitField?.value.trim() || item?.itemUnit || '';
            const remarks = remarksField?.value.trim() || '';

            if (!item || !Number.isFinite(quantity) || quantity <= 0 || quantity > available || (!isChemical && !Number.isInteger(quantity)) || (isChemical && unit === '')) {
                if (error) {
                    error.textContent = quantity > available ? `Quantity cannot exceed ${item?.itemAvailable || 'the available amount'}.` : (isChemical && unit === '' ? 'Enter the unit for this chemical.' : 'Enter a valid quantity.');
                    error.classList.remove('d-none');
                }
                quantityField?.focus();
                return;
            }

            const existingEntry = getEntry(item.itemType, item.itemId);
            if (existingEntry) {
                updateEntry(existingEntry, quantity, unit, remarks, item.itemType);
            } else {
                cartList.append(createEntry(item, quantity, unit, remarks));
            }

            syncCart();
            hideSelections();
            return;
        }

        const cancelButton = event.target.closest('[data-picker-cancel]');
        if (cancelButton && root.contains(cancelButton)) {
            hideSelections();
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
