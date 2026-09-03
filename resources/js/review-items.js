const escapeReviewHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const initializeReviewItemEditor = (root) => {
    if (root.dataset.reviewItemEditorInitialized === 'true') {
        return;
    }

    const form = root.closest('form');
    const selectedList = root.querySelector('[data-review-selected-items]');
    const removedItems = root.querySelector('[data-review-removed-items]');

    if (!form || !selectedList || !removedItems) {
        return;
    }

    root.dataset.reviewItemEditorInitialized = 'true';
    form.querySelector('[data-legacy-review-items]')?.querySelectorAll('input').forEach((input) => {
        input.disabled = true;
    });

    const tabPanes = new Map(
        Array.from(root.querySelectorAll('[data-review-tab-pane]')).map((pane) => [pane.dataset.reviewTabPane, pane]),
    );
    const removedExisting = new Map();
    const debounceTimers = new Map();
    const requests = new Map();
    const searchInput = root.querySelector('[data-review-item-search]');

    const itemKey = (itemType, itemId) => itemType + ':' + itemId;
    const getSelected = (itemType, itemId) => Array.from(selectedList.querySelectorAll('[data-review-selected-item]'))
        .find((row) => row.dataset.itemType === itemType && row.dataset.itemId === itemId);

    const syncEmptyState = () => {
        const hasItems = selectedList.querySelector('[data-review-selected-item]');
        selectedList.querySelector('[data-review-no-items]')?.remove();

        if (!hasItems) {
            selectedList.innerHTML = '<tr data-review-no-items><td colspan="4" class="text-center text-secondary py-3">No items selected.</td></tr>';
        }
    };

    const syncResults = () => {
        root.querySelectorAll('[data-review-available-item]').forEach((row) => {
            const selected = Boolean(getSelected(row.dataset.itemType, row.dataset.itemId));
            row.classList.toggle('is-selected', selected);

            const action = row.querySelector('[data-review-row-action]');
            if (action) {
                action.innerHTML = selected
                    ? '<i class="fa-solid fa-check me-1" aria-hidden="true"></i>Added'
                    : 'Select <i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i>';
            }
        });
    };

    const hideSelections = () => {
        root.querySelectorAll('[data-review-item-selection]').forEach((panel) => {
            panel.classList.add('d-none');
            panel.removeAttribute('data-item-type');
            panel.removeAttribute('data-item-id');
            panel.removeAttribute('data-item-name');
            panel.removeAttribute('data-item-code');
            panel.removeAttribute('data-item-available');
            panel.removeAttribute('data-item-unit');
            panel.querySelector('[data-review-selection-error]')?.classList.add('d-none');
        });
        root.querySelectorAll('[data-review-available-item].is-picking').forEach((row) => row.classList.remove('is-picking'));
    };

    const openSelection = (row) => {
        const panel = row.closest('[data-review-tab-pane]')?.querySelector('[data-review-item-selection]');

        if (!panel) {
            return;
        }

        hideSelections();
        panel.dataset.itemType = row.dataset.itemType;
        panel.dataset.itemId = row.dataset.itemId;
        panel.dataset.itemName = row.dataset.itemName;
        panel.dataset.itemCode = row.dataset.itemCode;
        panel.dataset.itemAvailable = row.dataset.itemAvailable;
        panel.dataset.itemUnit = row.dataset.itemUnit || '';
        panel.classList.remove('d-none');
        panel.querySelector('[data-review-selection-name]').textContent = row.dataset.itemName + ' (' + row.dataset.itemCode + ')';
        panel.querySelector('[data-review-selection-quantity]').value = '';
        const unitField = panel.querySelector('[data-review-selection-unit]');
        if (unitField) {
            unitField.value = row.dataset.itemUnit || '';
        }
        row.classList.add('is-picking');
        window.setTimeout(() => panel.querySelector('[data-review-selection-quantity]')?.focus(), 0);
    };

    const createSelectedRow = (item, quantity, unit, existingId = null) => {
        const row = document.createElement('tr');
        const isChemical = item.itemType === 'Chemical';
        const quantityStep = isChemical ? '0.01' : '1';
        const quantityMin = isChemical ? '0.01' : '1';
        const quantityName = existingId
            ? 'items[' + existingId + '][quantity]'
            : 'new_items[' + item.itemType + '][' + item.itemId + '][quantity]';
        const unitField = isChemical && !existingId
            ? '<input type="hidden" name="new_items[Chemical][' + item.itemId + '][unit]" value="' + escapeReviewHtml(unit) + '" data-review-new-unit>'
            : '';

        row.dataset.reviewSelectedItem = '';
        row.dataset.itemType = item.itemType;
        row.dataset.itemId = item.itemId;
        row.dataset.itemAvailable = item.itemAvailable;
        if (existingId) {
            row.dataset.existingItemId = existingId;
        }

        row.innerHTML = '<td><div class="fw-semibold text-dark">' + escapeReviewHtml(item.itemName) + '</div><div class="small text-secondary">' + escapeReviewHtml(item.itemCode) + '</div></td>'
            + '<td class="small text-secondary">' + escapeReviewHtml(item.itemType) + '</td>'
            + '<td style="max-width: 150px;"><input type="number" step="' + quantityStep + '" min="' + quantityMin + '" max="' + escapeReviewHtml(item.itemAvailable) + '" name="' + quantityName + '" value="' + escapeReviewHtml(quantity) + '" class="form-control form-control-sm text-end" required>' + unitField + '</td>'
            + '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-review-remove aria-label="Remove ' + escapeReviewHtml(item.itemName) + '"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></td>';

        return row;
    };

    const showSelectionError = (panel, message) => {
        const error = panel.querySelector('[data-review-selection-error]');
        if (error) {
            error.textContent = message;
            error.classList.remove('d-none');
        }
    };

    const buildResultsUrl = (pane, search = '', resetPage = false) => {
        const url = new URL(root.dataset.reviewResultsUrl, window.location.href);
        const itemType = pane.dataset.reviewTabPane;
        const pageParameter = itemType + '_page';

        url.searchParams.set('fragment', 'item-results');
        url.searchParams.set('item_type', itemType);
        if (search.trim()) {
            url.searchParams.set('search', search.trim());
        } else {
            url.searchParams.delete('search');
        }
        if (resetPage) {
            url.searchParams.delete(pageParameter);
        }

        return url.toString();
    };

    const replaceResults = async (pane, url) => {
        const results = pane.querySelector('[data-review-item-results]');
        if (!results) {
            return;
        }

        const paneName = pane.dataset.reviewTabPane;
        requests.get(paneName)?.abort();
        const controller = new AbortController();
        requests.set(paneName, controller);
        pane.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
            });

            if (!response.ok) {
                throw new Error('Unable to load available items.');
            }

            results.innerHTML = await response.text();
            syncResults();
        } finally {
            if (requests.get(paneName) === controller) {
                requests.delete(paneName);
                pane.removeAttribute('aria-busy');
            }
        }
    };

    root.addEventListener('click', async (event) => {
        const tabButton = event.target.closest('[data-review-tab-button]');
        if (tabButton && root.contains(tabButton)) {
            event.preventDefault();
            tabPanes.forEach((pane, key) => pane.classList.toggle('d-none', key !== tabButton.dataset.target));
            root.querySelectorAll('[data-review-tab-button]').forEach((button) => {
                const isActive = button === tabButton;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });
            hideSelections();
            const pane = tabPanes.get(tabButton.dataset.target);
            if (pane) {
                replaceResults(pane, buildResultsUrl(pane, searchInput?.value || '', true)).catch((error) => {
                    if (error.name !== 'AbortError') {
                        console.error('Unable to load available items.', error);
                    }
                });
            }
            return;
        }

        const availableRow = event.target.closest('[data-review-available-item]');
        if (availableRow && root.contains(availableRow)) {
            event.preventDefault();
            openSelection(availableRow);
            return;
        }

        const paginationLink = event.target.closest('[data-review-pagination] a');
        if (paginationLink && root.contains(paginationLink)) {
            event.preventDefault();
            const pane = paginationLink.closest('[data-review-tab-pane]');
            try {
                await replaceResults(pane, paginationLink.href);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Unable to load available items.', error);
                }
            }
            return;
        }

        const addButton = event.target.closest('[data-review-add]');
        if (addButton && root.contains(addButton)) {
            const panel = addButton.closest('[data-review-item-selection]');
            const item = panel ? {
                itemType: panel.dataset.itemType,
                itemId: panel.dataset.itemId,
                itemName: panel.dataset.itemName,
                itemCode: panel.dataset.itemCode,
                itemAvailable: panel.dataset.itemAvailable,
                itemUnit: panel.dataset.itemUnit || '',
            } : null;
            const quantityField = panel?.querySelector('[data-review-selection-quantity]');
            const quantity = Number(quantityField?.value);
            const available = Number(item?.itemAvailable);
            const unit = panel?.querySelector('[data-review-selection-unit]')?.value.trim() || item?.itemUnit || '';
            const isChemical = item?.itemType === 'Chemical';

            if (!item || !Number.isFinite(quantity) || quantity <= 0 || quantity > available || (!isChemical && !Number.isInteger(quantity)) || (isChemical && unit === '')) {
                showSelectionError(panel, quantity > available ? 'Quantity cannot exceed ' + (item?.itemAvailable || 'the available amount') + '.' : (isChemical && unit === '' ? 'Enter a unit for this chemical.' : 'Enter a valid quantity.'));
                quantityField?.focus();
                return;
            }

            const key = itemKey(item.itemType, item.itemId);
            if (getSelected(item.itemType, item.itemId)) {
                showSelectionError(panel, 'This item is already selected.');
                return;
            }

            const restoredExistingId = removedExisting.get(key);
            if (restoredExistingId) {
                removedItems.querySelector('[data-removed-item-key="' + CSS.escape(key) + '"]')?.remove();
                removedExisting.delete(key);
            }

            selectedList.querySelector('[data-review-no-items]')?.remove();
            selectedList.append(createSelectedRow(item, quantity, unit, restoredExistingId || null));
            syncResults();
            hideSelections();
            return;
        }

        const cancelButton = event.target.closest('[data-review-cancel]');
        if (cancelButton && root.contains(cancelButton)) {
            hideSelections();
            return;
        }

        const removeButton = event.target.closest('[data-review-remove]');
        if (removeButton && root.contains(removeButton)) {
            const row = removeButton.closest('[data-review-selected-item]');
            if (!row) {
                return;
            }

            const key = itemKey(row.dataset.itemType, row.dataset.itemId);
            if (row.dataset.existingItemId) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_items[]';
                hidden.value = row.dataset.existingItemId;
                hidden.dataset.removedItemKey = key;
                removedItems.append(hidden);
                removedExisting.set(key, row.dataset.existingItemId);
            }

            row.remove();
            syncEmptyState();
            syncResults();
        }
    });

    root.addEventListener('keydown', (event) => {
        const row = event.target.closest('[data-review-available-item]');
        if (row && root.contains(row) && ['Enter', ' '].includes(event.key)) {
            event.preventDefault();
            openSelection(row);
        }
    });

    root.addEventListener('input', (event) => {
        const searchInput = event.target.closest('[data-review-item-search]');
        if (!searchInput || !root.contains(searchInput)) {
            return;
        }

        const activeTab = root.querySelector('[data-review-tab-button].active')?.dataset.target || 'equipment';
        const pane = searchInput.closest('[data-review-tab-pane]') || tabPanes.get(activeTab);
        const paneName = pane?.dataset.reviewTabPane;
        if (!pane || !paneName) {
            return;
        }

        window.clearTimeout(debounceTimers.get(paneName));
        debounceTimers.set(paneName, window.setTimeout(async () => {
            try {
                await replaceResults(pane, buildResultsUrl(pane, searchInput.value, true));
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Unable to search available items.', error);
                }
            }
        }, 300));
    });

    syncEmptyState();
    syncResults();
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-review-item-editor]').forEach(initializeReviewItemEditor);
});
