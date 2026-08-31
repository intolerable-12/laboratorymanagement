(() => {
    const initializeCheckin = (root) => {
        if (!root || root.dataset.barcodeCheckinInitialized === 'true') {
            return;
        }

        const input = root.querySelector('#checkin-barcode');
        const form = root.querySelector('#checkin-scan-form');
        const startButton = root.querySelector('#start-checkin-scanner');
        const help = root.querySelector('#checkin-scanner-help');
        const feedback = root.querySelector('#checkin-ajax-feedback');
        const cart = root.querySelector('#checkin-cart');
        const statusBadge = root.querySelector('#checkin-status');
        const scanCount = root.querySelector('#checkin-scan-count');
        const cartCount = root.querySelector('#checkin-cart-count');
        const total = root.querySelector('#checkin-total');
        const returnedTotal = root.querySelector('#returned-total');
        const usedTotal = root.querySelector('#used-total');
        const quantityInput = root.querySelector('#checkin-quantity');
        const removeUrlTemplate = cart?.dataset.removeUrlTemplate;
        let requestInProgress = false;

        if (!input || !form || !startButton || !feedback || !cart || !removeUrlTemplate) {
            return;
        }

        root.dataset.barcodeCheckinInitialized = 'true';

        const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

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

        const manualField = (target) => target instanceof Element
            && Boolean(target.closest('#checkin-quantity, #condition_in, [data-barcode-manual-field]'));

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
            const conditionTone = scan.condition_in === 'Damaged' || scan.condition_in === 'Lost' ? 'danger' : 'success';
            row.innerHTML =
                '<div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">' +
                    '<i class="fa-solid fa-' + (scan.item_type === 'Chemical' ? 'flask' : 'microscope') + '"></i>' +
                '</div>' +
                '<div class="flex-grow-1 min-width-0">' +
                    '<div class="d-flex flex-wrap align-items-center gap-2">' +
                        '<span class="fw-semibold text-dark">' + escapeHtml(scan.item_name) + '</span>' +
                        '<span class="badge rounded-pill text-bg-light border text-secondary">' + escapeHtml(scan.item_type) + '</span>' +
                        '<span class="badge rounded-pill text-bg-' + conditionTone + '">' + escapeHtml(scan.condition_in) + '</span>' +
                    '</div>' +
                    '<div class="small text-secondary mt-1"><i class="fa-solid fa-barcode me-1"></i>' + escapeHtml(scan.barcode) + ' · ' + escapeHtml(formatTime(scan.scanned_at)) + '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-3 flex-shrink-0">' +
                    '<div class="text-end"><div class="fw-semibold text-dark">× ' + formatQuantity(scan.quantity, scan.item_type) + '</div><div class="small text-secondary">' + escapeHtml(scan.unit) + '</div></div>' +
                    '<button type="button" class="btn btn-sm btn-link text-danger p-1" data-remove-checkin="' + escapeHtml(scan.id) + '" title="Remove this scan" aria-label="Remove scan"><i class="fa-solid fa-trash-can"></i></button>' +
                '</div>';
            cart.prepend(row);
        };

        const updateProgress = (items) => {
            items.forEach((item) => {
                const row = root.querySelector('[data-checkin-key="' + item.key + '"]');

                if (!row) return;

                const precisionType = item.item_type;
                const complete = item.outstanding <= 0;
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
            const used = items.reduce((sum, item) => sum + Number(item.used), 0);

            total.dataset.accounted = accounted;
            total.textContent = formatQuantity(accounted, 'Chemical') + ' / ' + formatQuantity(checkedOut, 'Chemical');
            returnedTotal.textContent = formatQuantity(returned, 'Chemical');
            usedTotal.textContent = formatQuantity(used, 'Chemical');
        };

        const finishCheckin = () => {
            form.querySelectorAll('input, select, button').forEach((element) => {
                element.disabled = true;
            });
            startButton.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Check-in complete';
            help?.classList.add('d-none');
        };

        const reopenCheckin = () => {
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

            if (requestInProgress) return;

            requestInProgress = true;
            startButton.disabled = true;
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

                if (payload.complete) {
                    finishCheckin();
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
            const removeButton = event.target instanceof Element && event.target.closest('[data-remove-checkin]');

            if (!removeButton || requestInProgress) return;

            if (!window.confirm('Remove this item from the check-in cart?')) {
                focusScanner();
                return;
            }

            requestInProgress = true;
            removeButton.disabled = true;
            startButton.disabled = true;

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
                focusScanner();
            } finally {
                requestInProgress = false;
            }
        });
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
