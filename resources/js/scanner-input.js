const SCANNER_KEY_INTERVAL = 100;
const MIN_SCANNER_LENGTH = 3;

const isPrintableKey = (event) => event.key.length === 1
    && !event.ctrlKey
    && !event.metaKey
    && !event.altKey;

const isTextInputTarget = (target) => target instanceof HTMLInputElement
    || target instanceof HTMLTextAreaElement
    || (target instanceof Element && target.isContentEditable);

/**
 * Prevents a USB HID scanner's trailing Enter from submitting unrelated forms.
 *
 * The scanner behaves like a keyboard, so its Enter key would otherwise use
 * the browser's default form-submit behavior everywhere in the application.
 * Check-in/out and explicitly marked barcode searches are the only contexts
 * where that behavior is intentional.
 */
export const attachScannerEnterGuard = () => {
    let candidate = null;
    let expiryTimer = null;

    const clearCandidate = () => {
        candidate = null;
        window.clearTimeout(expiryTimer);
    };

    const isAllowedContext = (target) => target instanceof Element
        && Boolean(target.closest('[data-barcode-checkout], [data-barcode-checkin], [data-barcode-search]'));

    const handleKeydown = (event) => {
        if (!isTextInputTarget(event.target)) {
            clearCandidate();
            return;
        }

        if (event.key === 'Enter') {
            const isScannerEnter = candidate?.field === event.target
                && candidate.value.length >= MIN_SCANNER_LENGTH
                && candidate.isRapid;

            clearCandidate();

            if (isScannerEnter && !isAllowedContext(event.target)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }

            return;
        }

        if (!isPrintableKey(event)) {
            clearCandidate();
            return;
        }

        const now = performance.now();
        const isContinuation = candidate?.field === event.target
            && now - candidate.lastKeyAt <= SCANNER_KEY_INTERVAL;

        if (!isContinuation) {
            clearCandidate();
            candidate = {
                field: event.target,
                value: event.key,
                lastKeyAt: now,
                isRapid: false,
            };
        } else {
            candidate.value += event.key;
            candidate.isRapid = true;
            candidate.lastKeyAt = now;
        }

        window.clearTimeout(expiryTimer);
        expiryTimer = window.setTimeout(clearCandidate, SCANNER_KEY_INTERVAL + 25);
    };

    document.addEventListener('keydown', handleKeydown, true);

    return () => {
        clearCandidate();
        document.removeEventListener('keydown', handleKeydown, true);
    };
};

/**
 * Keeps USB HID scanner input working when a manual field has focus.
 *
 * Scanners send a rapid sequence of key events followed by Enter. Keystrokes
 * are held briefly so the first scanner characters cannot appear in a number
 * field; ordinary manual typing is replayed if the sequence is not a scan.
 */
export const attachScannerInputRouter = ({ root, barcodeInput, form, manualFieldSelector }) => {
    let candidate = null;
    let expiryTimer = null;

    const clearCandidate = () => {
        candidate = null;
        window.clearTimeout(expiryTimer);
    };

    const isManualField = (target) => target instanceof Element
        && root.contains(target)
        && Boolean(target.closest(manualFieldSelector));

    const snapshotField = (field) => ({
        value: field.value,
        selectionStart: field.selectionStart,
        selectionEnd: field.selectionEnd,
    });

    const restoreField = (field, snapshot) => {
        field.value = snapshot.value;

        if (typeof snapshot.selectionStart === 'number' && typeof field.setSelectionRange === 'function') {
            field.setSelectionRange(snapshot.selectionStart, snapshot.selectionEnd);
        }
    };

    const replayManualInput = (field, snapshot, value) => {
        restoreField(field, snapshot);

        const start = typeof snapshot.selectionStart === 'number' ? snapshot.selectionStart : field.value.length;
        const end = typeof snapshot.selectionEnd === 'number' ? snapshot.selectionEnd : start;

        try {
            field.setRangeText(value, start, end, 'end');
        } catch (error) {
            field.value = field.value.slice(0, start) + value + field.value.slice(end);
        }

        field.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const flushAsManualInput = () => {
        if (!candidate) {
            return;
        }

        const pending = candidate;
        clearCandidate();
        replayManualInput(pending.field, pending.snapshot, pending.value);
    };

    const completeScan = () => {
        const scannedBarcode = candidate?.value;

        if (!scannedBarcode || scannedBarcode.length < MIN_SCANNER_LENGTH || !candidate?.isRapid) {
            return false;
        }

        restoreField(candidate.field, candidate.snapshot);
        clearCandidate();
        barcodeInput.value = scannedBarcode;
        barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
        barcodeInput.focus({ preventScroll: true });
        barcodeInput.select();
        form.requestSubmit();

        return true;
    };

    const handleKeydown = (event) => {
        if (barcodeInput.disabled) {
            clearCandidate();
            return;
        }

        if (!isManualField(event.target)) {
            clearCandidate();
            return;
        }

        if (event.key === 'Enter') {
            if (candidate?.field === event.target && completeScan()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                flushAsManualInput();
            }

            return;
        }

        if (!isPrintableKey(event)) {
            flushAsManualInput();
            return;
        }

        // Keep scanner characters out of the quantity/condition field while
        // the sequence is being classified. Manual input is replayed below
        // if this turns out not to be a scan.
        event.preventDefault();

        const now = performance.now();
        const isContinuation = candidate?.field === event.target
            && now - candidate.lastKeyAt <= SCANNER_KEY_INTERVAL;

        if (!isContinuation) {
            flushAsManualInput();
            candidate = {
                field: event.target,
                snapshot: snapshotField(event.target),
                value: event.key,
                lastKeyAt: now,
                isRapid: false,
            };
        } else {
            candidate.value += event.key;
            candidate.isRapid = true;
            candidate.lastKeyAt = now;
        }

        window.clearTimeout(expiryTimer);
        expiryTimer = window.setTimeout(flushAsManualInput, SCANNER_KEY_INTERVAL + 25);
    };

    const handleFocusIn = (event) => {
        if (candidate && candidate.field !== event.target) {
            flushAsManualInput();
        }
    };

    document.addEventListener('keydown', handleKeydown, true);
    document.addEventListener('focusin', handleFocusIn, true);

    return () => {
        window.clearTimeout(expiryTimer);
        document.removeEventListener('keydown', handleKeydown, true);
        document.removeEventListener('focusin', handleFocusIn, true);
    };
};
