
import * as bootstrap from 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/themes/airbnb.css';
import Quill from 'quill';
import { attachScannerEnterGuard } from './scanner-input';
import './barcode';
import './checkin';
import './request-items';
import './review-items';

// Blade page scripts use Bootstrap's modal API for dynamically submitted forms.
window.bootstrap = bootstrap;

attachScannerEnterGuard();

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const toggleButtons = document.querySelectorAll('[data-admin-sidebar-toggle]');

    document.querySelectorAll('[data-barcode-size-controls]').forEach((controls) => {
        const sizeInput = controls.querySelector('[data-barcode-size]');
        const sizeOutput = controls.querySelector('[data-barcode-size-output]');
        const heightInput = controls.querySelector('[data-barcode-height]');
        const heightOutput = controls.querySelector('[data-barcode-height-output]');
        const printCard = controls.closest('.barcode-print-card');
        const barcodeGrid = printCard?.querySelector('.barcode-print-grid');
        const barcodeItems = barcodeGrid?.querySelectorAll('.barcode-print-item');

        if (!sizeInput || !sizeOutput || !heightInput || !heightOutput || !barcodeGrid || !barcodeItems?.length) {
            return;
        }

        const updateBarcodeSize = () => {
            const width = Number(sizeInput.value);
            const height = Number(heightInput.value);

            barcodeGrid.style.setProperty('--barcode-card-width', `${width}px`);
            barcodeItems.forEach((item) => {
                item.style.width = `min(100%, ${width}px)`;
                item.style.maxWidth = 'none';
                item.style.height = `${height}px`;
                item.style.minHeight = '0';
                item.style.maxHeight = 'none';
            });

            // Measure the rendered card after the requested dimensions have been
            // applied. This accounts for the page width, card padding, and print
            // margins so content never grows beyond the space actually available.
            const firstItem = barcodeItems[0];
            const itemStyles = window.getComputedStyle(firstItem);
            const horizontalPadding = parseFloat(itemStyles.paddingLeft) + parseFloat(itemStyles.paddingRight);
            const verticalPadding = parseFloat(itemStyles.paddingTop) + parseFloat(itemStyles.paddingBottom);
            const availableWidth = Math.max(1, firstItem.clientWidth - horizontalPadding);
            const availableHeight = Math.max(1, firstItem.clientHeight - verticalPadding);
            const contentScale = Math.min(availableWidth / 480, availableHeight / 220) * 0.98;

            barcodeGrid.style.setProperty('--barcode-content-scale', contentScale.toFixed(3));
            sizeOutput.textContent = `${width}px`;
            heightOutput.textContent = `${height}px`;
        };

        sizeInput.addEventListener('input', updateBarcodeSize);
        heightInput.addEventListener('input', updateBarcodeSize);
        updateBarcodeSize();
    });

    if (document.querySelector('[data-reservation-calendar-shell], [data-traceability-calendar-shell]')) {
        import('./calendar')
            .then(({ initializeCalendars, initializeTraceabilityCalendars }) => {
                initializeCalendars();
                initializeTraceabilityCalendars();
            })
            .catch((error) => console.error('Unable to initialize calendars.', error));
    }

    const requiredFieldSelector = 'input[required], select[required], textarea[required], [data-required-field="true"], [data-required-when]';

    const isConditionallyRequired = (field) => {
        if (field.matches(':disabled')) {
            return false;
        }

        if (!field.dataset.requiredWhen) {
            return field.matches('[required], [data-required-field="true"]');
        }

        const [dependencyName, ...expectedParts] = field.dataset.requiredWhen.split('=');
        const dependency = field.form?.elements.namedItem(dependencyName);

        return Boolean(dependency) && dependency.value === expectedParts.join('=');
    };

    const findRequiredFieldLabel = (field) => {
        if (field.id) {
            const labelScope = field.closest('form') ?? document;
            const matchingLabel = Array.from(labelScope.querySelectorAll('label[for]'))
                .find((label) => label.htmlFor === field.id);

            if (matchingLabel) {
                return matchingLabel;
            }
        }

        const fieldWrapper = field.closest('[data-rich-text-editor], .form-check, .mb-3, .col-md-6, .col-md-4, .col-12, .form-group');

        return fieldWrapper?.querySelector('label') ?? null;
    };

    const shouldManageRequiredIndicator = (field) => field.closest('form')?.dataset.requiredIndicators !== 'manual';

    const markRequiredLabel = (label) => {
        if (!label || label.querySelector('.required-indicator')) {
            return;
        }

        const indicator = document.createElement('span');
        indicator.className = 'required-indicator text-danger';
        indicator.dataset.generatedRequiredIndicator = 'true';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.textContent = '*';

        const accessibleText = document.createElement('span');
        accessibleText.className = 'visually-hidden';
        accessibleText.textContent = ' (required)';

        label.append(indicator, accessibleText);
    };

    const unmarkRequiredLabel = (label) => {
        const indicator = label?.querySelector('.required-indicator[data-generated-required-indicator="true"]');

        if (!indicator) {
            return;
        }

        const accessibleText = indicator.nextElementSibling;
        indicator.remove();
        if (accessibleText?.classList.contains('visually-hidden')) {
            accessibleText.remove();
        }
    };

    const decorateRequiredFields = (root = document) => {
        const fields = root.matches?.(requiredFieldSelector)
            ? [root]
            : Array.from(root.querySelectorAll(requiredFieldSelector));
        const forms = new Set();

        fields.forEach((field) => {
            const isRequired = isConditionallyRequired(field);

            if (field.dataset.requiredWhen) {
                field.toggleAttribute('required', isRequired);
            }

            field.setAttribute('aria-required', isRequired ? 'true' : 'false');

            if (shouldManageRequiredIndicator(field)) {
                const label = findRequiredFieldLabel(field);
                if (isRequired) {
                    markRequiredLabel(label);
                } else {
                    unmarkRequiredLabel(label);
                }
            }

            if (field.dataset.requiredWhen && !field.dataset.requiredWhenListener) {
                const dependencyName = field.dataset.requiredWhen.split('=')[0];
                const dependency = field.form?.elements.namedItem(dependencyName);

                dependency?.addEventListener('change', () => decorateRequiredFields(field.form));
                field.dataset.requiredWhenListener = 'true';
            }

            const form = field.closest('form');
            if (form && isRequired && shouldManageRequiredIndicator(field)) {
                forms.add(form);
            }
        });

        forms.forEach((form) => {
            if (form.querySelector('[data-required-fields-note]')) {
                return;
            }

            const note = document.createElement('p');
            note.className = 'required-fields-note text-secondary small mb-3';
            note.setAttribute('data-required-fields-note', 'true');
            note.innerHTML = '<span class="required-indicator text-danger" aria-hidden="true">*</span> Required fields';
            const noteContainer = form.querySelector('.card-body') ?? form;
            noteContainer.prepend(note);
        });
    };

    decorateRequiredFields();

    const requiredFieldObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    decorateRequiredFields(node);
                }
            });
        });
    });

    requiredFieldObserver.observe(document.body, { childList: true, subtree: true });

    const parseLocalDateTime = (value, isDateOnly) => {
        if (!value) {
            return null;
        }

        const parsedDate = isDateOnly ? new Date(`${value}T00:00:00`) : new Date(value);

        return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
    };

    const validateDateInput = (field) => {
        const value = field.value.trim();
        const messageElement = field.parentElement?.querySelector('[data-date-validation-message]');
        const setValidationMessage = (message) => {
            const hadClientError = field.dataset.clientValidationError === 'true';

            field.setCustomValidity(message);

            if (message) {
                field.classList.add('is-invalid');
                field.dataset.clientValidationError = 'true';
            } else if (hadClientError) {
                field.classList.remove('is-invalid');
                field.dataset.clientValidationError = 'false';
            }

            if (messageElement) {
                messageElement.textContent = message;
                messageElement.classList.toggle('d-none', !message);
                messageElement.classList.toggle('d-block', Boolean(message));
            }
        };

        if (!value) {
            setValidationMessage('');
            return;
        }

        const inputType = field.dataset.flatpickrType || field.type;
        const isDateOnly = inputType === 'date';
        const selectedDate = parseLocalDateTime(value, isDateOnly);

        if (!selectedDate) {
            setValidationMessage('Please enter a valid date.');
            return;
        }

        if (field.dataset.weekdayOnly === 'true') {
            const day = selectedDate.getDay();

            if (day === 0 || day === 6) {
                setValidationMessage('Borrow dates cannot fall on Saturday or Sunday.');
                return;
            }
        }

        if (field.dataset.labHours === 'borrow') {
            const day = selectedDate.getDay();
            const time = value.split('T')[1]?.slice(0, 5);

            if (day === 0) {
                setValidationMessage('Borrowing and returns are not available on Sundays.');
                return;
            }

            const windows = day === 6
                ? [{ opening: '08:00', closing: '12:00' }]
                : [{ opening: '07:30', closing: '12:00' }, { opening: '13:00', closing: '17:00' }];
            const hoursLabel = day === 6
                ? '8:00 AM to 12:00 PM'
                : '7:30 AM to 12:00 PM or 1:00 PM to 5:00 PM';

            const isWithinOperatingHours = time && windows.some((window) => time >= window.opening && time <= window.closing);

            if (!isWithinOperatingHours) {
                setValidationMessage(`This day is available from ${hoursLabel}.`);
                return;
            }
        }

        const minimumDateValue = field.dataset.businessDaysMin || field.min;

        if (minimumDateValue) {
            const minimumDate = parseLocalDateTime(minimumDateValue, isDateOnly);

            if (minimumDate && selectedDate < minimumDate) {
                setValidationMessage(field.dataset.minimumMessage || 'Please choose a later date.');
                return;
            }
        }

        setValidationMessage('');
    };

    document.querySelectorAll('input[type="date"][data-weekday-only], input[type="date"][data-business-days-min], input[type="datetime-local"][data-weekday-only], input[type="datetime-local"][data-lab-hours]').forEach((field) => {
        validateDateInput(field);
        field.addEventListener('input', () => validateDateInput(field));
        field.addEventListener('change', () => validateDateInput(field));
    });

    const flatpickrSelector = 'input[type="date"]:not([data-native-date-time]), input[type="time"]:not([data-native-date-time])';
    const normalizeTimeBound = (value) => /^\d{2}:\d{2}$/.test(value) ? `${value}:00` : value;
    const flatpickrPlaceholders = {
        reservation_date: 'Select Reservation Date',
        start_time: 'Select Start Time',
        end_time: 'Select End Time',
        birth_date: 'Select Birth Date',
        start_date: 'Select Start Date',
        end_date: 'Select End Date',
        manufactured_date: 'Select Manufactured Date',
        manufacturing_date: 'Select Manufacturing Date',
        received_date: 'Select Received Date',
        expiration_date: 'Select Expiration Date',
        transaction_date: 'Select Transaction Date',
        purchase_date: 'Select Purchase Date',
        date_from: 'Select From Date',
        date_to: 'Select To Date',
        from: 'Select From Date',
        to: 'Select To Date',
        list_from: 'Select List Start Date',
        list_to: 'Select List End Date',
    };

    const getFlatpickrPlaceholder = (field) => {
        const fieldName = field.name?.split('[').pop()?.replace(']', '') || '';

        if (flatpickrPlaceholders[fieldName]) {
            return flatpickrPlaceholders[fieldName];
        }

        const label = field.id
            ? document.querySelector(`label[for="${field.id}"]`)
            : field.closest('.mb-3, .col-md-6, .col-md-4, .col-12, .form-group')?.querySelector('label');
        const labelText = label?.textContent?.replace(/\s*\*\s*$/, '').trim();

        if (labelText) {
            return `Select ${labelText}`;
        }

        const fallbackName = fieldName.replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

        return fallbackName ? `Select ${fallbackName}` : 'Select date and time';
    };

    const addFlatpickrClearButton = (instance) => {
        if (!instance.calendarContainer || instance.calendarContainer.querySelector('[data-flatpickr-clear]')) {
            return;
        }

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'flatpickr-clear-button';
        clearButton.dataset.flatpickrClear = 'true';
        clearButton.textContent = 'Clear';
        clearButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            instance.clear();
            instance.close();
        });

        instance.calendarContainer.prepend(clearButton);
    };

    const initializeFlatpickr = (root = document) => {
        const fields = [];

        if (root.nodeType === Node.ELEMENT_NODE && root.matches(flatpickrSelector)) {
            fields.push(root);
        }

        if (root.querySelectorAll) {
            fields.push(...root.querySelectorAll(flatpickrSelector));
        }

        fields.forEach((field) => {
            if (field.dataset.flatpickrInitialized === 'true' || field._flatpickr) {
                return;
            }

            const inputType = field.type;
            const config = {
                disableMobile: true,
                allowInput: true,
                time_24hr: false,
                altInput: true,
            };
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');

            if (inputType === 'date') {
                config.dateFormat = 'Y-m-d';
                config.altFormat = 'F j, Y';

                if (min) {
                    config.minDate = min;
                }

                if (max) {
                    config.maxDate = max;
                }
            } else if (inputType === 'time') {
                config.noCalendar = true;
                config.enableTime = true;
                config.dateFormat = 'H:i';
                config.altFormat = 'h:i K';
                config.closeOnSelect = false;
                config.onReady = (_, __, instance) => addFlatpickrClearButton(instance);

                if (min) {
                    config.minTime = normalizeTimeBound(min);
                }

                if (max) {
                    config.maxTime = normalizeTimeBound(max);
                }

                const step = Number(field.getAttribute('step'));

                if (Number.isFinite(step) && step > 0) {
                    config.minuteIncrement = Math.max(1, Math.round(step / 60));
                }
            }

            const placeholder = field.getAttribute('placeholder') || getFlatpickrPlaceholder(field);

            field.setAttribute('placeholder', placeholder);
            field.dataset.flatpickrType = inputType;
            field.dataset.flatpickrInitialized = 'true';
            const instance = flatpickr(field, config);

            if (instance.altInput) {
                instance.altInput.placeholder = placeholder;
            }

            instance.calendarContainer?.querySelectorAll('.cur-year').forEach((yearInput) => {
                yearInput.tabIndex = 0;
                yearInput.inputMode = 'numeric';
                yearInput.title = 'Type a year or scroll to change it';

                yearInput.addEventListener('wheel', (event) => {
                    event.preventDefault();

                    const currentYear = Number.parseInt(yearInput.value, 10) || instance.currentYear;
                    const nextYear = currentYear + (event.deltaY < 0 ? 1 : -1);

                    instance.changeYear(nextYear);
                }, { passive: false });

                yearInput.addEventListener('change', () => {
                    const typedYear = Number.parseInt(yearInput.value, 10);

                    if (Number.isInteger(typedYear) && typedYear >= 1000) {
                        instance.changeYear(typedYear);
                    }
                });
            });
        });
    };

    initializeFlatpickr();

    const flatpickrObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    initializeFlatpickr(node);
                }
            });
        });
    });

    flatpickrObserver.observe(document.body, { childList: true, subtree: true });

    const richTextToolbarOptions = [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ script: 'sub' }, { script: 'super' }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote', 'code-block', 'link', 'clean'],
        [{ table: 'insert-table' }],
    ];

    const initializeRichTextEditors = (root = document) => {
        root.querySelectorAll('[data-rich-text-editor]').forEach((editor) => {
            if (editor.dataset.richTextInitialized === 'true') {
                return;
            }

            const input = editor.querySelector('[data-rich-text-input]');
            const surface = editor.querySelector('[data-rich-text-surface]');

            if (!input || !surface) {
                return;
            }

            editor.dataset.richTextInitialized = 'true';

            const quill = new Quill(surface, {
                theme: 'snow',
                placeholder: surface.dataset.placeholder || 'Write something meaningful...',
                modules: {
                    toolbar: {
                        container: richTextToolbarOptions,
                        handlers: {
                            table() {
                                const rows = Number.parseInt(window.prompt('How many rows should the table have?', '3'), 10);
                                const columns = Number.parseInt(window.prompt('How many columns should the table have?', '3'), 10);

                                if (!Number.isInteger(rows) || !Number.isInteger(columns) || rows < 1 || columns < 1 || rows > 20 || columns > 20) {
                                    return;
                                }

                                this.quill.getModule('table')?.insertTable(rows, columns);
                            },
                        },
                    },
                    table: true,
                },
            });

            const syncInput = () => {
                const html = quill.root.innerHTML;

                input.value = html === '<p><br></p>' ? '' : html;
            };

            if (input.value.trim() !== '') {
                quill.clipboard.dangerouslyPasteHTML(input.value);
            }

            syncInput();
            quill.on('text-change', syncInput);

            editor.closest('form')?.addEventListener('submit', syncInput);
        });
    };

    window.initRichTextEditors = initializeRichTextEditors;
    initializeRichTextEditors(document);

    const initializeImagePreviews = (root = document) => {
        root.querySelectorAll('[data-image-preview-input]').forEach((input) => {
            if (input.dataset.imagePreviewInitialized === 'true') {
                return;
            }

            const previewContainer = input.closest('[data-image-preview-container], .equipment-preview-card');
            const previewImage = previewContainer?.querySelector('[data-image-preview]');
            const placeholder = previewContainer?.querySelector('[data-image-preview-placeholder]');

            if (!previewImage) {
                return;
            }

            input.dataset.imagePreviewInitialized = 'true';

            const initialSrc = previewImage.dataset.imagePreviewInitialSrc || '';
            let objectUrl = null;

            const showPreview = (src) => {
                previewImage.src = src;
                previewImage.classList.remove('d-none');
                placeholder?.classList.add('d-none');
            };

            const resetPreview = () => {
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (initialSrc) {
                    showPreview(initialSrc);
                    return;
                }

                previewImage.removeAttribute('src');
                previewImage.classList.add('d-none');
                placeholder?.classList.remove('d-none');
            };

            input.addEventListener('change', () => {
                const file = input.files?.[0];

                if (!file) {
                    resetPreview();
                    return;
                }

                if (file.type && !file.type.startsWith('image/')) {
                    input.value = '';
                    resetPreview();
                    return;
                }

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                }

                objectUrl = URL.createObjectURL(file);
                showPreview(objectUrl);
            });
        });
    };

    window.initImagePreviews = initializeImagePreviews;
    initializeImagePreviews(document);

    const inventoryRailShells = document.querySelectorAll('[data-inventory-rail-shell]');

    inventoryRailShells.forEach((shell) => {
        const rail = shell.querySelector('[data-inventory-rail]');
        const prevButton = shell.querySelector('[data-inventory-rail-prev]');
        const nextButton = shell.querySelector('[data-inventory-rail-next]');

        if (!rail || shell.dataset.inventoryRailInitialized === 'true') {
            return;
        }

        shell.dataset.inventoryRailInitialized = 'true';

        const scrollStep = () => Math.max(Math.round(rail.clientWidth * 0.82), 320);

        const updateState = () => {
            const maxScrollLeft = Math.max(rail.scrollWidth - rail.clientWidth, 0);
            const isAtStart = rail.scrollLeft <= 4;
            const isAtEnd = rail.scrollLeft >= maxScrollLeft - 4;

            if (prevButton) {
                prevButton.disabled = isAtStart;
            }

            if (nextButton) {
                nextButton.disabled = isAtEnd;
            }
        };

        prevButton?.addEventListener('click', () => {
            rail.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
        });

        nextButton?.addEventListener('click', () => {
            rail.scrollBy({ left: scrollStep(), behavior: 'smooth' });
        });

        rail.addEventListener('scroll', () => {
            window.requestAnimationFrame(updateState);
        }, { passive: true });

        window.addEventListener('resize', updateState);
        updateState();
    });

    if (sidebar) {
        const storageKey = 'labcentral.admin.sidebar.open.v2';
        const updateToggleState = (isOpen) => {
            toggleButtons.forEach((button) => {
                button.classList.toggle('is-collapsed', !isOpen);
                button.setAttribute('aria-expanded', String(isOpen));

                const icon = button.querySelector('[data-sidebar-toggle-icon]');

                if (icon) {
                    icon.classList.toggle('fa-chevron-left', isOpen);
                    icon.classList.toggle('fa-chevron-right', !isOpen);
                }

                button.setAttribute('aria-label', isOpen ? 'Collapse sidebar' : 'Expand sidebar');
                button.title = isOpen ? 'Collapse sidebar' : 'Expand sidebar';
            });
        };

        const setSidebarState = (isOpen) => {
            body.classList.toggle('sidebar-collapsed', !isOpen);
            body.classList.toggle('coordinator-sidebar-open', isOpen);
            localStorage.setItem(storageKey, isOpen ? 'open' : 'closed');
            updateToggleState(isOpen);
        };

        const applyStoredState = () => {
            const storedState = localStorage.getItem(storageKey);

            setSidebarState(storedState !== 'closed');
        };

        applyStoredState();

        toggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const isOpen = body.classList.contains('sidebar-collapsed');
                setSidebarState(isOpen);
            });
        });
    }

    const reservationTabs = document.querySelector('[data-reservation-tabs]');

    if (reservationTabs) {
        const form = reservationTabs.closest('form');
        const laboratorySelect = form?.querySelector('select[name="laboratory_id"]');
        const tabButtons = reservationTabs.querySelectorAll('[data-reservation-tab-button]');
        const tabPanes = new Map(
            Array.from(reservationTabs.querySelectorAll('[data-reservation-tab-pane]')).map((pane) => [pane.dataset.reservationTabPane, pane]),
        );
        const fieldCache = new Map();
        const tabRequests = new Map();
        const searchDebounceTimers = new Map();

        const updateFieldCache = (scope = form) => {
            if (!scope) {
                return;
            }

            scope.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    fieldCache.set(field.name, field.checked ? field.value : '');
                    return;
                }

                fieldCache.set(field.name, field.value);
            });
        };

        const restoreCachedValues = (scope = form) => {
            if (!scope) {
                return;
            }

            scope.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
                if (!fieldCache.has(field.name)) {
                    return;
                }

                const cachedValue = fieldCache.get(field.name);

                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = cachedValue === field.value;
                    return;
                }

                field.value = cachedValue;
            });
        };

        const buildReservationFragmentUrl = (baseUrl, tabName, searchValue = null, resetPage = false) => {
            const url = new URL(baseUrl, window.location.href);
            const laboratoryId = laboratorySelect?.value;
            const search = String(searchValue ?? tabPanes.get(tabName)?.querySelector('[data-reservation-item-search]')?.value ?? '').trim();

            url.searchParams.set('fragment', tabName);

            if (search) {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }

            if (resetPage) {
                url.searchParams.delete(`${tabName}_page`);
            }

            if (laboratoryId) {
                url.searchParams.set('laboratory_id', laboratoryId);
            } else {
                url.searchParams.delete('laboratory_id');
            }

            return url.toString();
        };

        const setActiveTab = (tabName) => {
            tabButtons.forEach((button) => {
                const isActive = button.dataset.reservationTabButton === tabName || button.dataset.target === tabName;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });

            tabPanes.forEach((pane, key) => {
                pane.classList.toggle('show', key === tabName);
                pane.classList.toggle('active', key === tabName);
            });
        };

        const replaceTabContent = async (tabName, url) => {
            updateFieldCache(form);

            tabRequests.get(tabName)?.abort();
            const controller = new AbortController();
            tabRequests.set(tabName, controller);
            const pane = tabPanes.get(tabName);
            pane?.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load reservation items.');
                }

                const html = await response.text();

                if (!pane) {
                    return;
                }

                pane.innerHTML = html;
                restoreCachedValues(pane);
                pane.dispatchEvent(new CustomEvent('request-items-content-replaced', { bubbles: true }));
            } finally {
                if (tabRequests.get(tabName) === controller) {
                    tabRequests.delete(tabName);
                    pane?.removeAttribute('aria-busy');
                }
            }
        };

        const reloadReservationTabs = async (tabNames = Array.from(tabPanes.keys())) => {
            await Promise.all(tabNames.map((tabName) => {
                const pane = tabPanes.get(tabName);

                if (!pane) {
                    return Promise.resolve();
                }

                const baseUrl = window.location.href;

                return replaceTabContent(tabName, buildReservationFragmentUrl(baseUrl, tabName));
            }));
        };

        reservationTabs.addEventListener('click', async (event) => {
            const tabButton = event.target.closest('[data-reservation-tab-button]');

            if (tabButton) {
                event.preventDefault();
                setActiveTab(tabButton.dataset.target);
                return;
            }

            const paginationLink = event.target.closest('[data-reservation-pagination] a');

            if (!paginationLink) {
                return;
            }

            event.preventDefault();

            const pane = paginationLink.closest('[data-reservation-tab-pane]');
            const tabName = pane?.dataset.reservationTabPane;

            if (!tabName) {
                return;
            }

            try {
                await replaceTabContent(tabName, buildReservationFragmentUrl(paginationLink.href, tabName));
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                window.location.href = paginationLink.href;
            }
        });

        laboratorySelect?.addEventListener('change', async () => {
            reservationTabs.querySelectorAll('[data-item-picker]').forEach((itemPicker) => {
                itemPicker.dispatchEvent(new CustomEvent('request-items-laboratory-changed'));
            });

            try {
                await reloadReservationTabs();
            } catch (error) {
                window.location.reload();
            }
        });

        reservationTabs.addEventListener('input', (event) => {
            const field = event.target;

            if (field?.matches('[data-reservation-item-search]')) {
                const pane = field.closest('[data-reservation-tab-pane]');
                const tabName = pane?.dataset.reservationTabPane;

                if (!tabName) {
                    return;
                }

                window.clearTimeout(searchDebounceTimers.get(tabName));
                searchDebounceTimers.set(tabName, window.setTimeout(async () => {
                    try {
                        await replaceTabContent(
                            tabName,
                            buildReservationFragmentUrl(window.location.href, tabName, field.value, true),
                        );
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            console.error('Unable to search requested items.', error);
                        }
                    }
                }, 300));

                return;
            }

            if (field && field.name) {
                fieldCache.set(field.name, field.value);
            }
        });

        reservationTabs.addEventListener('change', (event) => {
            const field = event.target;

            if (field && field.name) {
                fieldCache.set(field.name, field.value);
            }
        });

        const initialTab = reservationTabs.querySelector('[data-reservation-tab-button].active')?.dataset.target || 'equipment';
        setActiveTab(initialTab);
        restoreCachedValues(form);
    }

    const setTextContent = (element, value, fallback = '-') => {
        if (!element) {
            return;
        }

        const resolved = value === null || value === undefined || value === '' ? fallback : value;
        element.textContent = resolved;
    };

    const announcementFeedShells = document.querySelectorAll('[data-announcement-feed-shell]');

    const renderAnnouncementBadges = (container, values) => {
        if (!container) {
            return;
        }

        container.replaceChildren();

        (values || []).forEach((value) => {
            const badge = document.createElement('span');
            badge.className = 'badge text-bg-primary-subtle text-primary-emphasis border';
            badge.textContent = value;
            container.appendChild(badge);
        });
    };

    const updateAnnouncementImageNavigation = (viewport, previousButton, nextButton) => {
        if (!viewport || !previousButton || !nextButton) {
            return;
        }

        const maxScroll = viewport.scrollWidth - viewport.clientWidth;

        previousButton.disabled = viewport.scrollLeft <= 1;
        nextButton.disabled = maxScroll <= 1 || viewport.scrollLeft >= maxScroll - 1;
    };

    const renderAnnouncementImages = (container, viewport, previousButton, nextButton, images) => {
        if (!container || !viewport || !previousButton || !nextButton) {
            return;
        }

        container.replaceChildren();

        if (!images || images.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'announcement-modal__images-empty text-secondary';
            empty.textContent = 'No images attached to this announcement.';
            container.appendChild(empty);
            viewport.scrollLeft = 0;
            previousButton.hidden = true;
            nextButton.hidden = true;
            return;
        }

        images.forEach((imageUrl) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'announcement-modal__image-item';

            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = 'Announcement image';
            image.className = 'img-fluid rounded-4 border w-100';

            wrapper.appendChild(image);
            container.appendChild(wrapper);
        });

        viewport.scrollLeft = 0;
        const hasOverflow = viewport.clientWidth === 0
            ? images.length > 2
            : viewport.scrollWidth > viewport.clientWidth + 1;
        previousButton.hidden = !hasOverflow;
        nextButton.hidden = !hasOverflow;
        updateAnnouncementImageNavigation(viewport, previousButton, nextButton);
    };

    announcementFeedShells.forEach((shell) => {
        const dataElement = shell.querySelector('[data-announcement-feed-data]');
        const modalElement = shell.querySelector('[data-announcement-feed-modal]');

        if (!dataElement || !modalElement || shell.dataset.announcementFeedInitialized === 'true') {
            return;
        }

        let announcements = [];

        try {
            announcements = JSON.parse(dataElement.textContent || '[]');
        } catch (error) {
            announcements = [];
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const modalSelectors = {
            title: modalElement.querySelector('[data-announcement-title]'),
            position: modalElement.querySelector('[data-announcement-position]'),
            campus: modalElement.querySelector('[data-announcement-campus]'),
            schedule: modalElement.querySelector('[data-announcement-schedule]'),
            audiences: modalElement.querySelector('[data-announcement-audiences]'),
            content: modalElement.querySelector('[data-announcement-content]'),
            images: modalElement.querySelector('[data-announcement-images]'),
            imagesViewport: modalElement.querySelector('[data-announcement-images-viewport]'),
            imagesPrevious: modalElement.querySelector('[data-announcement-images-previous]'),
            imagesNext: modalElement.querySelector('[data-announcement-images-next]'),
        };

        const openAnnouncement = (announcement) => {
            setTextContent(modalSelectors.title, announcement.title, 'Announcement details');

            setTextContent(modalSelectors.position, announcement.posted_by_role ?? 'Coordinator');
            setTextContent(modalSelectors.campus, announcement.posted_by_campus ?? 'MAIN');
            setTextContent(modalSelectors.schedule, announcement.start_date || announcement.end_date ? `${announcement.start_date || 'Anytime'} - ${announcement.end_date || 'Open ended'}` : `Updated ${announcement.updated_at || announcement.created_at || '-'}`);
            renderAnnouncementBadges(modalSelectors.audiences, announcement.audiences || []);

            if (modalSelectors.content) {
                modalSelectors.content.innerHTML = announcement.content || '';
            }

            renderAnnouncementImages(
                modalSelectors.images,
                modalSelectors.imagesViewport,
                modalSelectors.imagesPrevious,
                modalSelectors.imagesNext,
                announcement.images || [],
            );
            modal.show();

            window.requestAnimationFrame(() => {
                const hasOverflow = modalSelectors.imagesViewport.scrollWidth > modalSelectors.imagesViewport.clientWidth + 1;

                modalSelectors.imagesPrevious.hidden = !hasOverflow;
                modalSelectors.imagesNext.hidden = !hasOverflow;
                updateAnnouncementImageNavigation(
                    modalSelectors.imagesViewport,
                    modalSelectors.imagesPrevious,
                    modalSelectors.imagesNext,
                );
            });
        };

        modalSelectors.imagesPrevious?.addEventListener('click', () => {
            modalSelectors.imagesViewport?.scrollBy({
                left: -Math.max(modalSelectors.imagesViewport.clientWidth, 240),
                behavior: 'smooth',
            });
        });

        modalSelectors.imagesNext?.addEventListener('click', () => {
            modalSelectors.imagesViewport?.scrollBy({
                left: Math.max(modalSelectors.imagesViewport.clientWidth, 240),
                behavior: 'smooth',
            });
        });

        modalSelectors.imagesViewport?.addEventListener('scroll', () => {
            updateAnnouncementImageNavigation(
                modalSelectors.imagesViewport,
                modalSelectors.imagesPrevious,
                modalSelectors.imagesNext,
            );
        }, { passive: true });

        shell.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-announcement-trigger]');

            if (!trigger) {
                return;
            }

            event.preventDefault();

            const index = Number(trigger.dataset.announcementIndex);

            if (Number.isNaN(index) || !announcements[index]) {
                return;
            }

            openAnnouncement(announcements[index]);
        });

        shell.dataset.announcementFeedInitialized = 'true';
    });

    document.querySelectorAll('[data-shared-remarks]').forEach((remarksContainer) => {
        const remarksInput = remarksContainer.querySelector('[data-shared-remarks-input]');
        const remarksFields = remarksContainer.querySelectorAll('[data-shared-remarks-field]');

        if (!remarksInput || remarksFields.length === 0) {
            return;
        }

        const syncRemarks = () => {
            remarksFields.forEach((field) => {
                field.value = remarksInput.value;
            });
        };

        remarksInput.addEventListener('input', syncRemarks);
        remarksContainer.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', syncRemarks);
        });
        syncRemarks();
    });

    const liveSearchForms = document.querySelectorAll('[data-live-search-form]');

    liveSearchForms.forEach((form) => {
        const resultKey = form.dataset.liveSearchForm;
        const resultSelector = `[data-live-search-results="${resultKey}"]`;
        const searchInput = form.querySelector('input[name="search"]');
        let results = document.querySelector(resultSelector);
        let debounceTimer;
        let requestSequence = 0;

        if (!resultKey || !searchInput || !results) {
            return;
        }

        const buildFormUrl = () => {
            const url = new URL(form.action, window.location.href);
            const params = new URLSearchParams();

            new FormData(form).forEach((value, name) => {
                if (typeof value === 'string' && value !== '') {
                    params.append(name, value);
                }
            });

            params.delete('page');
            url.search = params.toString();

            return url;
        };

        const updateResults = async (targetUrl, historyMode = 'replace') => {
            const sequence = ++requestSequence;

            results.setAttribute('aria-busy', 'true');
            results.classList.add('opacity-50');

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load search results.');
                }

                const html = await response.text();
                const documentFragment = new DOMParser().parseFromString(html, 'text/html');
                const nextResults = documentFragment.querySelector(resultSelector);

                if (!nextResults) {
                    throw new Error('Search results container was not found.');
                }

                if (sequence !== requestSequence) {
                    return;
                }

                results.replaceWith(nextResults);
                results = nextResults;

                if (historyMode === 'push') {
                    window.history.pushState({}, '', targetUrl.pathname + targetUrl.search);
                } else if (historyMode === 'replace') {
                    window.history.replaceState({}, '', targetUrl.pathname + targetUrl.search);
                }
            } catch (error) {
                if (sequence === requestSequence) {
                    window.location.href = targetUrl.toString();
                }
            } finally {
                if (sequence === requestSequence) {
                    results.removeAttribute('aria-busy');
                    results.classList.remove('opacity-50');
                }
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(debounceTimer);
            updateResults(buildFormUrl(), 'push');
        });

        searchInput.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(() => {
                updateResults(buildFormUrl(), 'replace');
            }, 300);
        });

        document.addEventListener('click', (event) => {
            const paginationLink = event.target.closest(`${resultSelector} [data-live-search-pagination] a`);

            if (!paginationLink || !results.contains(paginationLink)) {
                return;
            }

            event.preventDefault();
            updateResults(new URL(paginationLink.href, window.location.href), 'push');
        });

        window.addEventListener('popstate', () => {
            const formPath = new URL(form.action, window.location.href).pathname;

            if (window.location.pathname === formPath) {
                updateResults(new URL(window.location.href), 'none');
            }
        });
    });
});
