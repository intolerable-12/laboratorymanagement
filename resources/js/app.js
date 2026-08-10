
import * as bootstrap from 'bootstrap';
import Quill from 'quill';

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const toggleButtons = document.querySelectorAll('[data-admin-sidebar-toggle]');

    const parseLocalDateTime = (value, isDateOnly) => {
        if (!value) {
            return null;
        }

        const parsedDate = isDateOnly ? new Date(`${value}T00:00:00`) : new Date(value);

        return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
    };

    const validateDateInput = (field) => {
        const value = field.value.trim();

        if (!value) {
            field.setCustomValidity('');
            return;
        }

        const selectedDate = parseLocalDateTime(value, field.type === 'date');

        if (!selectedDate) {
            field.setCustomValidity('Please enter a valid date.');
            return;
        }

        if (field.dataset.weekdayOnly === 'true') {
            const day = selectedDate.getDay();

            if (day === 0 || day === 6) {
                field.setCustomValidity('Weekends are not allowed.');
                return;
            }
        }

        const minimumDateValue = field.dataset.businessDaysMin || field.min;

        if (minimumDateValue) {
            const minimumDate = parseLocalDateTime(minimumDateValue, field.type === 'date');

            if (minimumDate && selectedDate < minimumDate) {
                field.setCustomValidity('Please choose a later date.');
                return;
            }
        }

        field.setCustomValidity('');
    };

    document.querySelectorAll('input[type="date"][data-weekday-only], input[type="date"][data-business-days-min], input[type="datetime-local"][data-weekday-only]').forEach((field) => {
        validateDateInput(field);
        field.addEventListener('input', () => validateDateInput(field));
        field.addEventListener('change', () => validateDateInput(field));
    });

    const richTextToolbarOptions = [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote', 'link', 'clean'],
    ];

    document.querySelectorAll('[data-rich-text-editor]').forEach((editor) => {
        const input = editor.querySelector('[data-rich-text-input]');
        const surface = editor.querySelector('[data-rich-text-surface]');

        if (!input || !surface) {
            return;
        }

        const quill = new Quill(surface, {
            theme: 'snow',
            placeholder: surface.dataset.placeholder || 'Write something meaningful...',
            modules: {
                toolbar: richTextToolbarOptions,
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

    if (sidebar) {
        const storageKey = 'labcentral.admin.sidebar.open.v2';
        const updateToggleState = (isOpen) => {
            toggleButtons.forEach((button) => {
                button.classList.toggle('is-collapsed', !isOpen);
                button.setAttribute('aria-pressed', String(isOpen));
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

        const buildReservationFragmentUrl = (baseUrl, tabName) => {
            const url = new URL(baseUrl, window.location.href);
            const laboratoryId = laboratorySelect?.value;

            url.searchParams.set('fragment', tabName);

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

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load reservation items.');
            }

            const html = await response.text();
            const pane = tabPanes.get(tabName);

            if (!pane) {
                return;
            }

            pane.innerHTML = html;
            restoreCachedValues(pane);
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
                window.location.href = paginationLink.href;
            }
        });

        laboratorySelect?.addEventListener('change', async () => {
            try {
                await reloadReservationTabs();
            } catch (error) {
                window.location.reload();
            }
        });

        reservationTabs.addEventListener('input', (event) => {
            const field = event.target;

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
});
