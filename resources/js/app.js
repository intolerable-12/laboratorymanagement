
import * as bootstrap from 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Quill from 'quill';
import './barcode';
import './checkin';
import './request-items';

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const toggleButtons = document.querySelectorAll('[data-admin-sidebar-toggle]');

    if (document.querySelector('[data-reservation-calendar-shell]')) {
        import('./calendar')
            .then(({ initializeCalendars }) => initializeCalendars())
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
            status: modalElement.querySelector('[data-announcement-status]'),
            author: modalElement.querySelector('[data-announcement-author]'),
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

            if (modalSelectors.status) {
                modalSelectors.status.className = `badge ${announcement.is_published ? 'text-bg-success' : 'text-bg-secondary'}`;
                modalSelectors.status.textContent = announcement.is_published ? 'Published' : 'Draft';
            }

            setTextContent(modalSelectors.author, `${announcement.posted_by ?? 'System'}${announcement.posted_by_role ? ` - ${announcement.posted_by_role}` : ''}`);
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
