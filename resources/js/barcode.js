(() => {
    const initializeBarcodeCheckout = (root) => {
        if (!root || root.dataset.barcodeCheckoutInitialized === 'true') {
            return;
        }

        const input = root.querySelector('#barcode');
        const form = root.querySelector('#checkout-scan-form');
        const startButton = root.querySelector('#start-scanner');
        const help = root.querySelector('#scanner-help');
        const feedback = root.querySelector('#ajax-feedback');
        const cart = root.querySelector('#scanned-cart');
        const statusBadge = root.querySelector('#checkout-status');
        const scanCount = root.querySelector('#scan-count');
        const cartCount = root.querySelector('#cart-count');
        const checkoutTotal = root.querySelector('#checkout-total');
        const quantityInput = root.querySelector('#quantity');
        const removeUrlTemplate = cart?.dataset.removeUrlTemplate;
        let requestInProgress = false;

        if (!input || !form || !startButton || !cart || !removeUrlTemplate) {
            return;
        }

        root.dataset.barcodeCheckoutInitialized = 'true';

        const focusScanner = () => {
            if (input.disabled) {
                return;
            }

            window.setTimeout(() => {
                if (input.disabled) {
                    return;
                }

                input.focus({ preventScroll: true });
                input.select();
                help?.classList.remove('d-none');
            }, 0);
        };

        const shouldKeepManualFocus = (target) => target instanceof Element
            && Boolean(target.closest('#quantity, #condition_out, [data-barcode-manual-field]'));

        // HID scanners send keystrokes to the focused element. Restore the barcode
        // field after page controls are used, while leaving manual form fields usable.
        document.addEventListener('click', (event) => {
            const clickedRemoveButton = event.target instanceof Element
                && event.target.closest('[data-remove-scan]');

            if (!shouldKeepManualFocus(event.target) && !clickedRemoveButton) {
                focusScanner();
            }
        });

        document.addEventListener('focusin', (event) => {
            if (event.target !== input && !shouldKeepManualFocus(event.target)) {
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
            : '—';

        const showFeedback = (message, type = 'success') => {
            feedback.className = 'alert alert-' + type + ' border-0 small';
            feedback.textContent = message;
        };

        const statusClass = (status) => status === 'Borrowed'
            ? 'text-bg-success'
            : (status === 'Partially Borrowed' ? 'text-bg-warning' : 'text-bg-primary');

        const parseJsonResponse = async (response) => {
            const body = await response.text();

            try {
                return body ? JSON.parse(body) : {};
            } catch (error) {
                throw new Error(response.ok
                    ? 'The server returned an invalid response.'
                    : 'The scan could not be completed. Please try again.');
            }
        };

        const addScanToCart = (scan) => {
            root.querySelector('#empty-cart')?.remove();

            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-3 py-3 border-bottom';
            row.dataset.scanRow = '';
            row.dataset.scanId = scan.id;
            row.innerHTML =
                '<div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">' +
                    '<i class="fa-solid fa-' + (scan.item_type === 'Chemical' ? 'flask' : 'microscope') + '"></i>' +
                '</div>' +
                '<div class="flex-grow-1 min-width-0">' +
                    '<div class="d-flex flex-wrap align-items-center gap-2">' +
                        '<span class="fw-semibold text-dark">' + escapeHtml(scan.item_name) + '</span>' +
                        '<span class="badge rounded-pill text-bg-light border text-secondary">' + escapeHtml(scan.item_type) + '</span>' +
                    '</div>' +
                    '<div class="small text-secondary mt-1">' +
                        '<i class="fa-solid fa-barcode me-1"></i>' + escapeHtml(scan.barcode) +
                        '<span class="mx-1">·</span>' + escapeHtml(formatTime(scan.scanned_at)) +
                    '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-3 flex-shrink-0">' +
                    '<div class="text-end">' +
                        '<div class="fw-semibold text-dark">× ' + formatQuantity(scan.quantity, scan.item_type) + '</div>' +
                        '<div class="small text-secondary">' + escapeHtml(scan.unit) + '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-link text-danger p-1 remove-scan" data-remove-scan="' + escapeHtml(scan.id) + '" title="Remove this scan" aria-label="Remove scan">' +
                        '<i class="fa-solid fa-trash-can"></i>' +
                    '</button>' +
                '</div>';
            cart.prepend(row);
        };

        const updateProgress = (items) => {
            items.forEach((item) => {
                const row = root.querySelector('[data-checklist-key="' + item.key + '"]');

                if (!row) {
                    return;
                }

                const current = row.querySelector('[data-progress-current]');
                const remaining = row.querySelector('[data-progress-remaining]');
                const complete = item.remaining <= 0;

                current.textContent = formatQuantity(item.checked_out, item.item_type) + ' / ' + formatQuantity(item.requested, item.item_type);
                current.classList.toggle('text-success', complete);
                current.classList.toggle('text-dark', !complete);
                remaining.textContent = complete ? 'Complete' : formatQuantity(item.remaining, item.item_type) + ' remaining';
                remaining.classList.toggle('text-success', complete);
                remaining.classList.toggle('text-secondary', !complete);
            });
        };

        const updateTotals = (items) => {
            const current = items.reduce((sum, item) => sum + Number(item.checked_out), 0);
            const requested = items.reduce((sum, item) => sum + Number(item.requested), 0);

            checkoutTotal.dataset.current = current;
            checkoutTotal.textContent = formatQuantity(current, 'Chemical') + ' / ' + formatQuantity(requested, 'Chemical');
        };

        const finishCheckout = () => {
            form.querySelectorAll('input, select, button').forEach((element) => {
                element.disabled = true;
            });
            startButton.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Checkout complete';
            help?.classList.add('d-none');
        };

        const reopenCheckout = () => {
            form.querySelectorAll('input, select, button').forEach((element) => {
                element.disabled = false;
            });
            startButton.innerHTML = '<i class="fa-solid fa-barcode me-1"></i> Start scanner';
        };

        startButton.addEventListener('click', focusScanner);
        window.addEventListener('load', focusScanner);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (requestInProgress) {
                return;
            }

            requestInProgress = true;
            startButton.disabled = true;
            startButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Checking out...';
            feedback.className = 'd-none';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: new FormData(form),
                });
                const payload = await parseJsonResponse(response);

                if (!response.ok) {
                    const messages = Object.values(payload.errors ?? {}).flat();
                    throw new Error(messages.join(' ') || payload.message || 'The scan could not be completed.');
                }

                addScanToCart(payload.scan);
                updateProgress(payload.items);
                updateTotals(payload.items);
                scanCount.textContent = Number(scanCount.textContent) + 1;
                cartCount.textContent = Number(cartCount.textContent) + 1;
                statusBadge.textContent = payload.status;
                statusBadge.className = 'badge px-3 py-2 ' + statusClass(payload.status);
                showFeedback(payload.message);

                if (payload.complete) {
                    finishCheckout();
                } else {
                    input.value = '';
                    quantityInput.value = '';
                    startButton.disabled = false;
                    startButton.innerHTML = '<i class="fa-solid fa-barcode me-1"></i> Start scanner';
                    focusScanner();
                }
            } catch (error) {
                showFeedback(error.message, 'danger');
                startButton.disabled = false;
                startButton.innerHTML = '<i class="fa-solid fa-barcode me-1"></i> Start scanner';
                focusScanner();
            } finally {
                requestInProgress = false;
            }
        });

        cart.addEventListener('click', async (event) => {
            const removeButton = event.target.closest('[data-remove-scan]');

            if (!removeButton || requestInProgress) {
                return;
            }

            if (!window.confirm('Remove this item from the checkout cart?')) {
                focusScanner();
                return;
            }

            requestInProgress = true;
            removeButton.disabled = true;
            startButton.disabled = true;

            try {
                const scanId = removeButton.dataset.removeScan;
                const response = await fetch(removeUrlTemplate.replace('__SCAN__', encodeURIComponent(scanId)), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const payload = await parseJsonResponse(response);

                if (!response.ok) {
                    const messages = Object.values(payload.errors ?? {}).flat();
                    throw new Error(messages.join(' ') || payload.message || 'The cart item could not be removed.');
                }

                cart.querySelector('[data-scan-id="' + scanId + '"]')?.remove();

                if (!cart.querySelector('[data-scan-row]')) {
                    cart.innerHTML = '<div id="empty-cart" class="text-center py-5"><div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;"><i class="fa-solid fa-cart-shopping fa-lg"></i></div><h3 class="h5 fw-semibold text-dark">Cart is empty</h3><p class="small text-secondary mb-0">Scanned equipment and chemicals will appear here.</p></div>';
                }

                updateProgress(payload.items);
                updateTotals(payload.items);
                scanCount.textContent = payload.scan_count;
                cartCount.textContent = payload.scan_count;
                statusBadge.textContent = payload.status;
                statusBadge.className = 'badge px-3 py-2 ' + statusClass(payload.status);
                showFeedback(payload.message);
                reopenCheckout();
                focusScanner();
            } catch (error) {
                showFeedback(error.message, 'danger');
                removeButton.disabled = false;
                startButton.disabled = false;
                focusScanner();
            } finally {
                requestInProgress = false;
            }
        });
    };

    const initialize = () => {
        document.querySelectorAll('[data-barcode-checkout]').forEach(initializeBarcodeCheckout);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
