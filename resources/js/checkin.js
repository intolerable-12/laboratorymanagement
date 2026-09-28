import * as bootstrap from 'bootstrap';

(() => {
    const initializeCheckin = (root) => {
        if (!root || root.dataset.barcodeCheckinInitialized === 'true') {
            return;
        }

        const input = root.querySelector('#checkin-barcode');
        const form = root.querySelector('#checkin-scan-form');
        const startButton = root.querySelector('#start-checkin-scanner');
        const stopButton = root.querySelector('#stop-checkin-scanner');
        const help = root.querySelector('#checkin-scanner-help');
        const feedback = root.querySelector('#checkin-ajax-feedback');
        const cart = root.querySelector('#checkin-cart');
        const filterTabs = root.querySelectorAll('[data-scan-filter]');
        const statusBadge = root.querySelector('#checkin-status');
        const scanCount = root.querySelector('#checkin-scan-count');
        const cartCount = root.querySelector('#checkin-cart-count');
        const total = root.querySelector('#checkin-total');
        const returnedTotal = root.querySelector('#returned-total');
        const quantityModalElement = document.getElementById('checkin-quantity-modal');
        const quantityInput = document.getElementById('checkin-quantity');
        const submitButton = document.getElementById('submit-checkin-scan');
        const conditionInput = document.getElementById('condition_in');
        const itemNameElement = document.querySelector('[data-checkin-item-name]');
        const itemStateLabelElement = document.querySelector('[data-checkin-item-state-label]');
        const itemStateElement = document.querySelector('[data-checkin-item-state]');
        const unitElement = document.querySelector('[data-checkin-unit]');
        const conditionLabelElement = document.querySelector('[data-checkin-condition-label]');
        const maximumElement = document.querySelector('[data-checkin-quantity-maximum]');
        const quantityErrorElement = document.querySelector('[data-checkin-quantity-error]');
        const remarksWrapper = document.querySelector('[data-checkin-remarks-wrapper]');
        const remarksInput = document.querySelector('#checkin-remarks');
        const remarksErrorElement = document.querySelector('[data-checkin-remarks-error]');
        const quantityModal = quantityModalElement
            ? bootstrap.Modal.getOrCreateInstance(quantityModalElement)
            : null;
        const completionModalElement = document.getElementById('checkin-complete-modal');
        const removeUrlTemplate = cart?.dataset.removeUrlTemplate;
        const scannerAvailable = Boolean(input && startButton && !input.disabled && !startButton.disabled);
        let requestInProgress = false;
        let scannerActive = false;
        let activeFilter = 'all';
        let activeItemType = '';

        if (!input || !form || !startButton || !stopButton || !quantityInput || !conditionInput || !feedback || !cart || !removeUrlTemplate) {
            return;
        }

        root.dataset.barcodeCheckinInitialized = 'true';

        const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        const updateScannerControls = () => {
            startButton.classList.toggle('d-none', scannerActive);
            stopButton.classList.toggle('d-none', !scannerActive);
            help?.classList.toggle('d-none', !scannerActive);
        };

        const focusScanner = () => {
            if (!scannerActive || input.disabled) {
                return;
            }

            input.focus({ preventScroll: true });
            input.select();
            help?.classList.remove('d-none');
        };

        const hideQuantityField = () => {
            quantityModal?.hide();
            quantityInput.value = '';
            quantityInput.max = '';
            quantityInput.disabled = true;
            conditionInput.disabled = true;
            activeItemType = '';
            remarksWrapper?.classList.add('d-none');
            if (remarksInput) {
                remarksInput.value = '';
                remarksInput.disabled = true;
                remarksInput.required = false;
                remarksInput.setCustomValidity('');
            }
            remarksErrorElement?.classList.add('d-none');
            quantityErrorElement?.classList.add('d-none');
            quantityInput.classList.remove('is-invalid');
            quantityInput.setCustomValidity('');
            if (submitButton) {
                submitButton.disabled = true;
            }
        };

        const syncRemarksRequirement = () => {
            const hasScannedItem = activeItemType !== '';
            const requiresRemarks = hasScannedItem
                && ['Lost', 'Damaged'].includes(conditionInput.value);

            remarksWrapper?.classList.toggle('d-none', !hasScannedItem);

            if (!remarksInput) {
                return requiresRemarks;
            }

            remarksInput.disabled = !requiresRemarks;
            remarksInput.required = requiresRemarks;

            if (!requiresRemarks) {
                remarksInput.value = '';
                remarksInput.setCustomValidity('');
                remarksErrorElement?.classList.add('d-none');
                remarksInput.classList.remove('is-invalid');
            }

            return requiresRemarks;
        };

        const showQuantityField = () => {
            const barcode = input.value.trim();
            const item = [...root.querySelectorAll('[data-checkin-barcode]')]
                .find((row) => row.dataset.checkinBarcode === barcode);

            if (!item || item.dataset.itemType !== 'Equipment') {
                hideQuantityField();
                showFeedback('Only equipment can be checked in. Chemicals are consumed during reservations and are not returned.', 'danger');
                input.value = '';
                focusScanner();
                return;
            }

            activeItemType = item?.dataset.itemType || '';
            const maximum = Number(item?.dataset.itemMaximum ?? 0);

            if (itemNameElement) {
                itemNameElement.textContent = item?.dataset.itemName || 'Scanned item';
            }

            if (unitElement) {
                unitElement.textContent = item?.dataset.itemUnit || 'unit(s)';
            }

            if (maximumElement) {
                maximumElement.textContent = 'Maximum: ' + formatQuantity(maximum, activeItemType) + ' ' + (item?.dataset.itemUnit || 'unit(s)');
            }

            const stateLabel = item?.dataset.itemStateLabel || 'Equipment condition';

            if (itemStateLabelElement) {
                itemStateLabelElement.textContent = stateLabel;
            }

            if (itemStateElement) {
                itemStateElement.textContent = item?.dataset.itemState || 'Unknown';
            }

            if (conditionLabelElement) {
                conditionLabelElement.textContent = stateLabel;
            }

            quantityInput.value = '';
            quantityInput.max = String(maximum);
            quantityInput.classList.remove('is-invalid');
            quantityErrorElement?.classList.add('d-none');
            quantityInput.setCustomValidity('');
            quantityInput.disabled = false;
            conditionInput.value = 'Good';
            conditionInput.disabled = false;
            if (remarksInput) {
                remarksInput.value = '';
            }
            syncRemarksRequirement();
            if (submitButton) {
                submitButton.disabled = false;
            }
            quantityModal?.show();
        };

        quantityModalElement?.addEventListener('shown.bs.modal', () => {
            quantityInput.focus({ preventScroll: true });
        });

        quantityModalElement?.addEventListener('hidden.bs.modal', () => {
            if (requestInProgress || quantityInput.disabled) {
                return;
            }

            input.value = '';
            quantityInput.value = '';
            quantityInput.disabled = true;
            conditionInput.disabled = true;
            if (submitButton) {
                submitButton.disabled = true;
            }
            activeItemType = '';
            remarksWrapper?.classList.add('d-none');
            if (remarksInput) {
                remarksInput.value = '';
                remarksInput.disabled = true;
                remarksInput.required = false;
                remarksInput.setCustomValidity('');
            }
            remarksErrorElement?.classList.add('d-none');
            focusScanner();
        });

        const startScanning = () => {
            if (!scannerAvailable || requestInProgress) {
                return;
            }

            scannerActive = true;
            input.disabled = false;
            updateScannerControls();
            focusScanner();
        };

        const stopScanning = () => {
            scannerActive = false;
            input.value = '';
            hideQuantityField();
            input.disabled = true;
            input.blur();
            updateScannerControls();
        };

        if (scannerAvailable) {
            input.disabled = true;
        }
        updateScannerControls();

        const manualField = (target) => target instanceof Element
            && Boolean(target.closest('#checkin-quantity, #condition_in, #checkin-remarks, #checkin-quantity-modal, [data-barcode-manual-field], [data-scan-filter]'));

        document.addEventListener('click', (event) => {
            const clickedRemove = event.target instanceof Element && event.target.closest('[data-remove-checkin]');

            if (!manualField(event.target) && !clickedRemove) {
                focusScanner();
            }
        });

        document.addEventListener('focusin', (event) => {
            if (event.target !== input && !manualField(event.target)) {
                focusScanner();
            }
        });

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        };

        const formatQuantity = (value, type) => Number(value).toLocaleString(undefined, {
            minimumFractionDigits: type === 'Chemical' ? 2 : 0,
            maximumFractionDigits: type === 'Chemical' ? 2 : 0,
        });

        const formatTime = (value) => value
            ? new Date(value).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
            : '-';

        const parseJsonResponse = async (response) => {
            const body = await response.text();

            try {
                return body ? JSON.parse(body) : {};
            } catch (error) {
                throw new Error(response.ok
                    ? 'The server returned an invalid response.'
                    : 'The check-in could not be completed. Please try again.');
            }
        };

        const showFeedback = (message, type = 'success') => {
            feedback.className = 'alert alert-' + type + ' border-0 small';
            feedback.textContent = message;
        };

        const setQuantityError = (message) => {
            quantityInput.classList.add('is-invalid');
            quantityInput.setCustomValidity(message);
            if (quantityErrorElement) {
                quantityErrorElement.textContent = message;
                quantityErrorElement.classList.remove('d-none');
            }
        };

        const clearQuantityError = () => {
            quantityInput.classList.remove('is-invalid');
            quantityInput.setCustomValidity('');
            quantityErrorElement?.classList.add('d-none');
        };

        const validateQuantity = (showError = true) => {
            const rawQuantity = quantityInput.value.trim();
            const maximum = Number(quantityInput.max);
            const quantity = Number(rawQuantity);

            if (rawQuantity === '') {
                if (!showError) {
                    clearQuantityError();
                    return false;
                }

                setQuantityError('Enter the returned quantity before confirming.');
            } else if (!Number.isFinite(quantity) || quantity <= 0) {
                setQuantityError('Enter a returned quantity greater than zero.');
            } else if (Number.isFinite(maximum) && quantity > maximum) {
                setQuantityError('Quantity cannot exceed the maximum of ' + formatQuantity(maximum, activeItemType) + '.');
            } else {
                clearQuantityError();
                return true;
            }

            if (!showError) {
                return false;
            }

            quantityInput.reportValidity();
            quantityInput.focus({ preventScroll: true });
            return false;
        };

        const setRemarksError = (message) => {
            remarksInput?.classList.add('is-invalid');
            remarksInput?.setCustomValidity(message);
            if (remarksErrorElement) {
                remarksErrorElement.textContent = message;
                remarksErrorElement.classList.remove('d-none');
            }
        };

        const validateRemarks = () => {
            if (!syncRemarksRequirement()) {
                return true;
            }

            if (remarksInput?.value.trim() !== '') {
                remarksInput.setCustomValidity('');
                remarksInput.classList.remove('is-invalid');
                remarksErrorElement?.classList.add('d-none');
                return true;
            }

            const message = 'Remarks are required when the item is marked Lost or Damaged.';
            setRemarksError(message);
            remarksInput?.reportValidity();
            remarksInput?.focus({ preventScroll: true });
            return false;
        };

        quantityInput.addEventListener('input', () => validateQuantity(false));
        conditionInput.addEventListener('change', syncRemarksRequirement);
        remarksInput?.addEventListener('input', () => {
            if (remarksInput.value.trim() !== '') {
                remarksInput.classList.remove('is-invalid');
                remarksInput.setCustomValidity('');
                remarksErrorElement?.classList.add('d-none');
            }
        });

        const updateCartFilter = () => {
            const rows = [...cart.querySelectorAll('[data-checkin-row]')];
            const visibleRows = rows.filter((row) => activeFilter === 'all' || row.dataset.scanCondition === activeFilter);

            filterTabs.forEach((tab) => {
                const filter = tab.dataset.scanFilter;
                const count = filter === 'all'
                    ? rows.length
                    : rows.filter((row) => row.dataset.scanCondition === filter).length;
                const countElement = tab.querySelector('[data-scan-filter-count]');

                if (countElement) {
                    countElement.textContent = count;
                }

                const isActive = filter === activeFilter;
                tab.classList.toggle('btn-primary', isActive);
                tab.classList.toggle('btn-outline-secondary', !isActive);
                tab.setAttribute('aria-pressed', String(isActive));

                if (countElement) {
                    countElement.classList.toggle('bg-white', isActive);
                    countElement.classList.toggle('text-primary', isActive);
                    countElement.classList.toggle('bg-secondary', !isActive);
                    countElement.classList.toggle('text-white', !isActive);
                }
            });

            rows.forEach((row) => {
                const isVisible = visibleRows.includes(row);
                row.classList.toggle('d-none', !isVisible);
                row.classList.toggle('border-bottom', isVisible && row !== visibleRows[visibleRows.length - 1]);
            });

            let filteredEmpty = cart.querySelector('[data-filter-empty]');

            if (!rows.length) {
                filteredEmpty?.remove();
                return;
            }

            if (!filteredEmpty) {
                filteredEmpty = document.createElement('div');
                filteredEmpty.dataset.filterEmpty = '';
                filteredEmpty.className = 'text-center text-secondary small py-4 d-none';
                cart.append(filteredEmpty);
            }

            filteredEmpty.textContent = 'No ' + activeFilter.toLowerCase() + ' check-in scans yet.';
            filteredEmpty.classList.toggle('d-none', visibleRows.length > 0);
        };

        filterTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activeFilter = tab.dataset.scanFilter || 'all';
                updateCartFilter();
            });
        });

        const statusClass = (status) => {
            if (status === 'Returned') return 'text-bg-success';
            if (status === 'Partially Returned') return 'text-bg-warning';
            if (status === 'Overdue') return 'text-bg-danger';
            return 'text-bg-primary';
        };

        const addScanToCart = (scan) => {
            root.querySelector('#empty-checkin-cart')?.remove();

            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-3 py-3 border-bottom';
            row.dataset.checkinRow = '';
            row.dataset.checkinId = scan.id;
            row.dataset.scanCondition = scan.condition_in || 'Good';
            const condition = row.dataset.scanCondition;
            const conditionClass = ({
                Excellent: 'text-bg-success',
                Good: 'text-bg-primary',
                Fair: 'text-bg-warning text-dark',
                Damaged: 'equipment-condition-badge--damaged',
                Lost: 'text-bg-danger',
            }[condition] || 'text-bg-danger');
            row.innerHTML =
                '<div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">' +
                    '<i class="fa-solid fa-' + (scan.item_type === 'Chemical' ? 'flask' : 'microscope') + '"></i>' +
                '</div>' +
                '<div class="flex-grow-1 min-width-0">' +
                    '<div class="d-flex flex-wrap align-items-center gap-2">' +
                        '<span class="fw-semibold text-dark">' + escapeHtml(scan.item_name) + '</span>' +
                        '<span class="badge rounded-pill text-bg-light border text-secondary">' + escapeHtml(scan.item_type) + '</span>' +
                        '<span class="badge rounded-pill ' + conditionClass + '">' + escapeHtml(condition) + '</span>' +
                    '</div>' +
                    '<div class="small text-secondary mt-1"><i class="fa-solid fa-barcode me-1"></i>' + escapeHtml(scan.barcode) + ' · ' + escapeHtml(formatTime(scan.scanned_at)) + '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-3 flex-shrink-0">' +
                    '<div class="text-end"><div class="fw-semibold text-dark">× ' + formatQuantity(scan.quantity, scan.item_type) + '</div><div class="small text-secondary">' + escapeHtml(scan.unit) + '</div></div>' +
                    '<button type="button" class="btn btn-sm btn-link text-danger p-1" data-remove-checkin="' + escapeHtml(scan.id) + '" title="Remove this scan" aria-label="Remove scan"><i class="fa-solid fa-trash-can"></i></button>' +
                '</div>';
            cart.prepend(row);
            updateCartFilter();
        };

        const updateProgress = (items) => {
            items.forEach((item) => {
                const row = root.querySelector('[data-checkin-key="' + item.key + '"]');

                if (!row) return;

                const precisionType = item.item_type;
                const complete = item.outstanding <= 0;
                row.dataset.itemMaximum = String(Math.max(0, Number(item.outstanding)));
                row.querySelector('[data-progress-returned]').textContent = formatQuantity(item.returned, precisionType);
                row.querySelector('[data-progress-used]').textContent = formatQuantity(item.used, precisionType);
                row.querySelector('[data-progress-damaged]').textContent = formatQuantity(item.damaged, precisionType);
                row.querySelector('[data-progress-lost]').textContent = formatQuantity(item.lost, precisionType);
                const accounted = row.querySelector('[data-progress-accounted]');
                const outstanding = row.querySelector('[data-progress-outstanding]');
                accounted.textContent = formatQuantity(item.accounted, precisionType) + ' / ' + formatQuantity(item.checked_out, precisionType);
                accounted.classList.toggle('text-success', complete);
                accounted.classList.toggle('text-dark', !complete);
                outstanding.textContent = complete ? 'Complete' : formatQuantity(item.outstanding, precisionType) + ' remaining';
                outstanding.classList.toggle('text-success', complete);
                outstanding.classList.toggle('text-secondary', !complete);
            });
        };

        const updateTotals = (items) => {
            const accounted = items.reduce((sum, item) => sum + Number(item.accounted), 0);
            const checkedOut = items.reduce((sum, item) => sum + Number(item.checked_out), 0);
            const returned = items.reduce((sum, item) => sum + Number(item.returned), 0);

            total.dataset.accounted = accounted;
            total.textContent = formatQuantity(accounted, 'Equipment') + ' / ' + formatQuantity(checkedOut, 'Equipment');
            returnedTotal.textContent = formatQuantity(returned, 'Equipment');
        };

        const finishCheckin = () => {
            stopScanning();
            form.querySelectorAll('input, select, button').forEach((element) => {
                element.disabled = true;
            });
            conditionInput.disabled = true;
            startButton.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Check-in complete';
            help?.classList.add('d-none');
            if (completionModalElement) {
                bootstrap.Modal.getOrCreateInstance(completionModalElement).show();
            }
        };

        const reopenCheckin = () => {
            form.querySelectorAll('input, select, button').forEach((element) => {
                element.disabled = false;
            });
            input.disabled = !scannerActive;
            hideQuantityField();
            startButton.innerHTML = '<i class="fa-solid fa-barcode me-1"></i> Start scanner';
            updateScannerControls();
        };

        startButton.addEventListener('click', startScanning);
        stopButton.addEventListener('click', stopScanning);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();

                if (input.value.trim() !== '') {
                    showQuantityField();
                }
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!validateQuantity() || !validateRemarks()) {
                return;
            }

            if (requestInProgress) return;

            requestInProgress = true;
            quantityModal?.hide();
            startButton.disabled = true;
            stopButton.disabled = true;
            if (submitButton) {
                submitButton.disabled = true;
            }
            startButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Checking in...';
            feedback.className = 'd-none';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: new FormData(form),
                });
                const payload = await parseJsonResponse(response);

                if (!response.ok) {
                    const messages = Object.values(payload.errors ?? {}).flat();
                    throw new Error(messages.join(' ') || payload.message || 'The check-in could not be completed.');
                }

                addScanToCart(payload.scan);
                updateProgress(payload.items);
                updateTotals(payload.items);
                scanCount.textContent = payload.scan_count;
                cartCount.textContent = payload.scan_count;
                statusBadge.textContent = payload.status;
                statusBadge.className = 'badge px-3 py-2 ' + statusClass(payload.status);
                showFeedback(payload.message);

                const allItemsComplete = payload.items?.length > 0
                    && payload.items.every((item) => Number(item.outstanding) <= 0);

                if (payload.complete && allItemsComplete) {
                    finishCheckin();
                } else {
                    input.value = '';
                    hideQuantityField();
                    startButton.disabled = false;
                    stopButton.disabled = false;
                    startButton.innerHTML = '<i class="fa-solid fa-barcode me-1"></i> Start scanner';
                    focusScanner();
                }
            } catch (error) {
                if (error?.message) {
                    if (error.message.toLowerCase().includes('quantity')) {
                        setQuantityError(error.message);
                    } else if (error.message.toLowerCase().includes('remarks')) {
                        setRemarksError(error.message);
                    }
                }
                showFeedback(error.message, 'danger');
                startButton.disabled = false;
                stopButton.disabled = false;
                if (submitButton) {
                    submitButton.disabled = false;
                }
                startButton.innerHTML = '<i class="fa-solid fa-barcode me-1"></i> Start scanner';
                quantityModal?.show();
            } finally {
                requestInProgress = false;
            }
        });

        cart.addEventListener('click', async (event) => {
            const removeButton = event.target instanceof Element && event.target.closest('[data-remove-checkin]');

            if (!removeButton || requestInProgress) return;

            if (!window.confirm('Remove this item from the check-in cart?')) {
                focusScanner();
                return;
            }

            requestInProgress = true;
            removeButton.disabled = true;
            startButton.disabled = true;
            stopButton.disabled = true;

            try {
                const scanId = removeButton.dataset.removeCheckin;
                const response = await fetch(removeUrlTemplate.replace('__SCAN__', encodeURIComponent(scanId)), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });
                const payload = await parseJsonResponse(response);

                if (!response.ok) {
                    const messages = Object.values(payload.errors ?? {}).flat();
                    throw new Error(messages.join(' ') || payload.message || 'The check-in line could not be removed.');
                }

                cart.querySelector('[data-checkin-id="' + scanId + '"]')?.remove();
                if (!cart.querySelector('[data-checkin-row]')) {
                    cart.innerHTML = '<div id="empty-checkin-cart" class="text-center py-5"><div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;"><i class="fa-solid fa-rotate-left fa-lg"></i></div><h3 class="h5 fw-semibold text-dark">Cart is empty</h3><p class="small text-secondary mb-0">Scanned returned equipment and chemicals will appear here.</p></div>';
                }
                updateCartFilter();
                updateProgress(payload.items);
                updateTotals(payload.items);
                scanCount.textContent = payload.scan_count;
                cartCount.textContent = payload.scan_count;
                statusBadge.textContent = payload.status;
                statusBadge.className = 'badge px-3 py-2 ' + statusClass(payload.status);
                showFeedback(payload.message);
                reopenCheckin();
                focusScanner();
            } catch (error) {
                showFeedback(error.message, 'danger');
                removeButton.disabled = false;
                startButton.disabled = false;
                stopButton.disabled = false;
                focusScanner();
            } finally {
                requestInProgress = false;
            }
        });

        updateCartFilter();
    };

    const initialize = () => {
        document.querySelectorAll('[data-barcode-checkin]').forEach(initializeCheckin);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
