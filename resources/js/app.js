
import * as bootstrap from 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
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
    };

    window.initRichTextEditors = initializeRichTextEditors;
    initializeRichTextEditors(document);

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

    const reservationCalendarShells = document.querySelectorAll('[data-reservation-calendar-shell]');

    const statusBadgeClass = (status) => {
        switch (status) {
            case 'Coordinator Approved':
                return 'text-bg-success';
            case 'Completed':
                return 'text-bg-info';
            default:
                return 'text-bg-primary';
        }
    };

    const setTextContent = (element, value, fallback = '-') => {
        if (!element) {
            return;
        }

        const resolved = value === null || value === undefined || value === '' ? fallback : value;
        element.textContent = resolved;
    };

    const renderReservationItems = (body, items) => {
        if (!body) {
            return;
        }

        body.replaceChildren();

        if (!items || items.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');

            cell.colSpan = 5;
            cell.className = 'text-center text-secondary py-4';
            cell.textContent = 'No items were attached to this reservation.';
            row.appendChild(cell);
            body.appendChild(row);
            return;
        }

        items.forEach((item) => {
            const row = document.createElement('tr');

            const typeCell = document.createElement('td');
            typeCell.textContent = item.type ?? '—';

            const nameCell = document.createElement('td');
            const itemName = document.createElement('div');
            itemName.className = 'fw-semibold text-dark';
            itemName.textContent = item.name ?? '—';
            const itemCode = document.createElement('div');
            itemCode.className = 'small text-secondary';
            itemCode.textContent = item.code ?? '—';
            nameCell.append(itemName, itemCode);

            const quantityCell = document.createElement('td');
            quantityCell.textContent = item.quantity ?? '—';

            const unitCell = document.createElement('td');
            unitCell.textContent = item.unit ?? '—';

            const remarksCell = document.createElement('td');
            remarksCell.textContent = item.remarks ?? '—';

            row.append(typeCell, nameCell, quantityCell, unitCell, remarksCell);
            body.appendChild(row);
        });
    };

    reservationCalendarShells.forEach((shell) => {
        const calendarElement = shell.querySelector('[data-reservation-calendar]');
        const eventsElement = shell.querySelector('[data-reservation-calendar-events]');
        const modalElement = shell.querySelector('[data-reservation-calendar-modal]');

        if (!calendarElement || !eventsElement || calendarElement.dataset.reservationCalendarInitialized === 'true') {
            return;
        }

        let events = [];

        try {
            events = JSON.parse(eventsElement.textContent || '[]');
        } catch (error) {
            events = [];
        }

        const modal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
        const modalSelectors = modalElement
            ? {
                title: modalElement.querySelector('[data-reservation-calendar-title]'),
                badge: modalElement.querySelector('[data-reservation-calendar-status]'),
                reservationNo: modalElement.querySelector('[data-reservation-reservation-no]'),
                studentName: modalElement.querySelector('[data-reservation-student-name]'),
                studentId: modalElement.querySelector('[data-reservation-student-id]'),
                studentEmail: modalElement.querySelector('[data-reservation-student-email]'),
                laboratoryName: modalElement.querySelector('[data-reservation-laboratory-name]'),
                laboratoryCode: modalElement.querySelector('[data-reservation-laboratory-code]'),
                experimentTitle: modalElement.querySelector('[data-reservation-experiment-title]'),
                reservationDate: modalElement.querySelector('[data-reservation-date]'),
                reservationTime: modalElement.querySelector('[data-reservation-time]'),
                expectedParticipants: modalElement.querySelector('[data-reservation-participants]'),
                schoolYear: modalElement.querySelector('[data-reservation-school-year]'),
                semester: modalElement.querySelector('[data-reservation-semester]'),
                purpose: modalElement.querySelector('[data-reservation-purpose]'),
                remarks: modalElement.querySelector('[data-reservation-remarks]'),
                itemsBody: modalElement.querySelector('[data-reservation-items-body]'),
            }
            : {};

        const calendar = new Calendar(calendarElement, {
            plugins: [dayGridPlugin, timeGridPlugin],
            initialView: 'dayGridMonth',
            timeZone: 'UTC',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay',
            },
            views: {
                dayGridMonth: {
                    dayMaxEventRows: 3,
                },
                timeGridWeek: {
                    slotMinTime: '06:00:00',
                    slotMaxTime: '22:00:00',
                },
                timeGridDay: {
                    slotMinTime: '06:00:00',
                    slotMaxTime: '22:00:00',
                },
            },
            height: 'auto',
            expandRows: true,
            nowIndicator: true,
            navLinks: true,
            selectable: false,
            editable: false,
            dayMaxEventRows: true,
            events,
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short',
            },
            eventContent(info) {
                const wrapper = document.createElement('div');
                wrapper.className = 'reservation-calendar-event';

                if (info.timeText) {
                    const time = document.createElement('div');
                    time.className = 'reservation-calendar-event__time';
                    time.textContent = info.timeText;
                    wrapper.appendChild(time);
                }

                const marquee = document.createElement('div');
                marquee.className = 'reservation-calendar-event__marquee';

                const track = document.createElement('div');
                track.className = 'reservation-calendar-event__track';

                const title = info.event.title || '';
                const first = document.createElement('span');
                first.textContent = title;
                const second = document.createElement('span');
                second.setAttribute('aria-hidden', 'true');
                second.textContent = title;

                track.append(first, second);
                marquee.appendChild(track);
                wrapper.appendChild(marquee);

                return { domNodes: [wrapper] };
            },
            eventClick(info) {
                info.jsEvent.preventDefault();

                if (!modal || !modalSelectors.title) {
                    return;
                }

                const props = info.event.extendedProps ?? {};
                const title = `${props.reservation_no ?? 'Reservation'} - ${props.laboratory_name ?? 'Laboratory'}`;

                setTextContent(modalSelectors.title, title, 'Reservation details');
                setTextContent(modalSelectors.reservationNo, props.reservation_no);
                setTextContent(modalSelectors.studentName, props.student_name);
                setTextContent(modalSelectors.studentId, props.student_id);
                setTextContent(modalSelectors.studentEmail, props.student_email);
                setTextContent(modalSelectors.laboratoryName, props.laboratory_name);
                setTextContent(modalSelectors.laboratoryCode, props.laboratory_code);
                setTextContent(modalSelectors.experimentTitle, props.experiment_title);
                setTextContent(modalSelectors.reservationDate, props.reservation_date);
                setTextContent(modalSelectors.reservationTime, `${props.start_time ?? '-'} - ${props.end_time ?? '-'}`);
                setTextContent(modalSelectors.expectedParticipants, props.expected_participants);
                setTextContent(modalSelectors.schoolYear, props.school_year);
                setTextContent(modalSelectors.semester, props.semester);
                setTextContent(modalSelectors.purpose, props.purpose);
                setTextContent(modalSelectors.remarks, props.remarks || 'No remarks provided.');

                if (modalSelectors.badge) {
                    modalSelectors.badge.className = `badge ${statusBadgeClass(props.status)}`;
                    modalSelectors.badge.textContent = props.status ?? 'Scheduled';
                }

                renderReservationItems(modalSelectors.itemsBody, props.items ?? []);
                modal.show();
            },
            eventDidMount(info) {
                info.el.classList.add('reservation-calendar__event');
            },
        });

        calendar.render();
        calendarElement.dataset.reservationCalendarInitialized = 'true';
    });

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

    const renderAnnouncementImages = (container, images) => {
        if (!container) {
            return;
        }

        container.replaceChildren();

        if (!images || images.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'col-12 text-secondary';
            empty.textContent = 'No images attached to this announcement.';
            container.appendChild(empty);
            return;
        }

        images.forEach((imageUrl) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'col-12 col-md-6';

            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = 'Announcement image';
            image.className = 'img-fluid rounded-4 border w-100';

            wrapper.appendChild(image);
            container.appendChild(wrapper);
        });
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

            renderAnnouncementImages(modalSelectors.images, announcement.images || []);
            modal.show();
        };

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
});
