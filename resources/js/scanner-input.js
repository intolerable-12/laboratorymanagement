const SCANNER_KEY_INTERVAL = 100;
const MIN_SCANNER_LENGTH = 3;

const isPrintableKey = (event) => event.key.length === 1
    && !event.ctrlKey
    && !event.metaKey
    && !event.altKey;

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
        barcodeInput.value = scannedBarcode;
        barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
        barcodeInput.focus({ preventScroll: true });
        barcodeInput.select();
        clearCandidate();
        form.requestSubmit();

        return true;
    };

    const handleKeydown = (event) => {
        if (barcodeInput.disabled || !isManualField(event.target)) {
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

    document.addEventListener('keydown', handleKeydown, true);

    return () => {
        window.clearTimeout(expiryTimer);
        document.removeEventListener('keydown', handleKeydown, true);
    };
};
