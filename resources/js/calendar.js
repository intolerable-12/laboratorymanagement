import * as bootstrap from 'bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

const toLocalDateString = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const setTextContent = (element, value, fallback = '-') => {
    if (!element) {
        return;
    }

    const resolved = value === null || value === undefined || value === '' ? fallback : value;
    element.textContent = resolved;
};

const statusBadgeClass = (status, eventType = 'reservation') => {
    if (eventType === 'borrow' && status === 'Coordinator Approved') {
        return 'text-bg-primary';
    }

    switch (status) {
        case 'Coordinator Approved':
            return 'text-bg-success';
        case 'Completed':
        case 'Partially Borrowed':
        case 'Borrowed':
        case 'Partially Returned':
        case 'Returned':
        case 'Overdue':
            return 'text-bg-info';
        default:
            return 'text-bg-primary';
    }
};

const renderReservationItems = (body, items, eventType) => {
    if (!body) {
        return;
    }

    body.replaceChildren();
    const isBorrow = eventType === 'borrow';
    const table = body.closest('table');

    table?.querySelectorAll('[data-borrow-only]').forEach((cell) => {
        cell.hidden = !isBorrow;
    });

    if (!items || items.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');

        cell.colSpan = isBorrow ? 6 : 3;
        cell.className = 'text-center text-secondary py-4';
        cell.textContent = `No items were attached to this ${isBorrow ? 'borrow request' : 'reservation'}.`;
        row.appendChild(cell);
        body.appendChild(row);
        return;
    }

    items.forEach((item) => {
        const row = document.createElement('tr');

        const nameCell = document.createElement('td');
        const typeBadge = document.createElement('span');
        typeBadge.className = 'badge calendar-item-type mb-1';
        typeBadge.textContent = item.type ?? 'Item';
        const itemName = document.createElement('div');
        itemName.className = 'fw-semibold text-dark';
        itemName.textContent = item.name ?? 'â€”';
        const itemCode = document.createElement('div');
        itemCode.className = 'small text-secondary';
        itemCode.textContent = item.code ?? 'â€”';
        nameCell.append(typeBadge, itemName, itemCode);

        const quantityCell = document.createElement('td');
        quantityCell.className = 'text-nowrap';
        quantityCell.textContent = `${item.quantity ?? 'â€”'} ${item.unit ?? ''}`.trim();

        const checkedOutCell = document.createElement('td');
        checkedOutCell.className = 'text-nowrap';
        checkedOutCell.textContent = `${item.checked_out ?? 'â€”'} ${item.unit ?? ''}`.trim();
        checkedOutCell.hidden = !isBorrow;

        const conditionCell = document.createElement('td');
        conditionCell.textContent = item.condition_out ?? 'â€”';
        conditionCell.hidden = !isBorrow;

        const progressCell = document.createElement('td');
        progressCell.className = 'calendar-item-progress';
        const addProgressBadge = (label, value, tone) => {
            const badge = document.createElement('span');
            badge.className = `badge text-bg-${tone}`;
            badge.textContent = value === '' ? label : `${label}: ${value}`;
            progressCell.appendChild(badge);
        };
        const checkedOutQuantity = Number.parseFloat(item.checked_out) || 0;

        if (item.type === 'Chemical') {
            if (checkedOutQuantity > 0) {
                addProgressBadge('Consumed', `${item.checked_out} ${item.unit ?? ''}`.trim(), 'info');
            } else {
                addProgressBadge('Not consumed', '', 'light');
            }
        } else if (checkedOutQuantity <= 0) {
            addProgressBadge('Not checked out', '', 'light');
        } else {
            const accountedQuantities = [
                ['Returned', item.returned, 'success'],
                ['Used', item.used, 'warning'],
                ['Lost', item.lost, 'danger'],
                ['Damaged', item.damaged, 'danger'],
            ];
            let hasAccountedQuantity = false;

            accountedQuantities.forEach(([label, rawQuantity, tone]) => {
                const quantity = Number.parseFloat(rawQuantity) || 0;
                if (quantity > 0) {
                    addProgressBadge(label, quantity, tone);
                    hasAccountedQuantity = true;
                }
            });

            if (item.condition_in && item.condition_in !== 'â€”') {
                addProgressBadge('Condition in', item.condition_in, 'secondary');
            } else if (!hasAccountedQuantity) {
                addProgressBadge('Awaiting check-in', '', 'light');
            }
        }
        progressCell.hidden = !isBorrow;

        const remarksCell = document.createElement('td');
        remarksCell.textContent = item.remarks ?? 'â€”';

        row.append(nameCell, quantityCell, checkedOutCell, conditionCell, progressCell, remarksCell);
        body.appendChild(row);
    });
};

export const initializeCalendars = () => {
    const reservationCalendarShells = document.querySelectorAll('[data-reservation-calendar-shell]');

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
                typeBadge: modalElement.querySelector('[data-reservation-calendar-type]'),
                badge: modalElement.querySelector('[data-reservation-calendar-status]'),
                reservationNo: modalElement.querySelector('[data-reservation-reservation-no]'),
                requesterLabel: modalElement.querySelector('[data-reservation-requester-label]'),
                studentName: modalElement.querySelector('[data-reservation-student-name]'),
                studentId: modalElement.querySelector('[data-reservation-student-id]'),
                studentEmail: modalElement.querySelector('[data-reservation-student-email]'),
                studentContact: modalElement.querySelector('[data-reservation-student-contact]'),
                studentDepartment: modalElement.querySelector('[data-reservation-student-department]'),
                laboratoryName: modalElement.querySelector('[data-reservation-laboratory-name]'),
                laboratoryCode: modalElement.querySelector('[data-reservation-laboratory-code]'),
                laboratoryLocation: modalElement.querySelector('[data-reservation-laboratory-location]'),
                campus: modalElement.querySelector('[data-reservation-campus]'),
                experimentTitle: modalElement.querySelector('[data-reservation-experiment-title]'),
                reservationDate: modalElement.querySelector('[data-reservation-date]'),
                scheduleLabel: modalElement.querySelector('[data-reservation-schedule-label]'),
                reservationTime: modalElement.querySelector('[data-reservation-time]'),
                checkedOutAt: modalElement.querySelector('[data-reservation-checked-out-at]'),
                releasedBy: modalElement.querySelector('[data-reservation-released-by]'),
                receivedBy: modalElement.querySelector('[data-reservation-received-by]'),
                assignedInstructor: modalElement.querySelector('[data-reservation-assigned-instructor]'),
                submittedAt: modalElement.querySelector('[data-reservation-submitted-at]'),
                updatedAt: modalElement.querySelector('[data-reservation-updated-at]'),
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
            timeZone: 'local',
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
            dayMaxEventRows: 2,
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
                const isBorrow = props.event_type === 'borrow';
                const entryType = isBorrow ? 'Borrow Request' : 'Reservation';
                const referenceNo = props.reference_no ?? props.reservation_no ?? entryType;
                const marker = props.schedule_marker ? ` (${props.schedule_marker})` : '';
                const title = `${entryType} - ${referenceNo} - ${props.laboratory_name ?? 'Laboratory'}${marker}`;

                setTextContent(modalSelectors.title, title, 'Schedule details');
                setTextContent(modalSelectors.typeBadge, entryType);
                if (modalSelectors.typeBadge) {
                    modalSelectors.typeBadge.className = `badge ${isBorrow ? 'text-bg-primary' : 'text-bg-success'} fs-6 px-3 py-2`;
                }
                setTextContent(modalSelectors.reservationNo, props.reservation_no);

                // "Student" by default, but label it "Instructor" when the requester is one.
                const requesterRole = props.requester_role ?? 'Student';
                setTextContent(
                    modalSelectors.requesterLabel,
                    requesterRole === 'Instructor' ? 'Instructor' : (requesterRole === 'Guest' ? 'Guest' : 'Student'),
                    'Requester'
                );

                setTextContent(modalSelectors.studentName, props.student_name);
                setTextContent(modalSelectors.studentId, props.student_id);
                setTextContent(modalSelectors.studentEmail, props.student_email);
                setTextContent(modalSelectors.studentContact, props.student_contact);
                setTextContent(modalSelectors.studentDepartment, props.student_department);
                setTextContent(modalSelectors.laboratoryName, props.laboratory_name);
                setTextContent(modalSelectors.laboratoryCode, props.laboratory_code);
                setTextContent(modalSelectors.laboratoryLocation, props.laboratory_location);
                setTextContent(modalSelectors.campus, props.campus);
                setTextContent(modalSelectors.experimentTitle, props.experiment_title);
                setTextContent(modalSelectors.reservationDate, props.reservation_date);
                setTextContent(modalSelectors.reservationTime, `${props.start_time ?? '-'} - ${props.end_time ?? '-'}`);
                setTextContent(modalSelectors.checkedOutAt, props.checked_out_at);
                setTextContent(modalSelectors.releasedBy, props.released_by);
                setTextContent(modalSelectors.receivedBy, props.received_by);
                setTextContent(modalSelectors.assignedInstructor, props.assigned_instructor);
                setTextContent(modalSelectors.submittedAt, props.submitted_at);
                setTextContent(modalSelectors.updatedAt, props.updated_at);
                setTextContent(modalSelectors.expectedParticipants, props.expected_participants);
                setTextContent(modalSelectors.schoolYear, props.school_year);
                setTextContent(modalSelectors.semester, props.semester);
                setTextContent(modalSelectors.purpose, props.purpose);
                setTextContent(modalSelectors.remarks, props.remarks || 'No remarks provided.');

                if (modalSelectors.badge) {
                    modalSelectors.badge.className = `badge ${statusBadgeClass(props.status, props.event_type)}`;
                    modalSelectors.badge.textContent = props.status_label ?? props.status ?? 'Scheduled';
                }

                renderReservationItems(modalSelectors.itemsBody, props.items ?? [], props.event_type);
                modal.show();
            },
            eventDidMount(info) {
                info.el.classList.add('reservation-calendar__event');
            },
        });

        calendar.render();
        calendarElement.dataset.reservationCalendarInitialized = 'true';
    });
};

const setTraceabilityDateInput = (input, value) => {
    if (input) {
        input.value = value;
    }
};

export const initializeTraceabilityCalendars = () => {
    const shells = document.querySelectorAll('[data-traceability-calendar-shell]');

    shells.forEach((shell) => {
        const calendarElement = shell.querySelector('[data-traceability-calendar]');
        const eventsElement = shell.querySelector('[data-traceability-calendar-events]');
        const rangeForm = shell.querySelector('[data-traceability-range-form]');
        const monthInput = shell.querySelector('[data-traceability-month]');
        const fromInput = shell.querySelector('[data-traceability-from]');
        const toInput = shell.querySelector('[data-traceability-to]');

        if (!calendarElement || !eventsElement || calendarElement.dataset.traceabilityCalendarInitialized === 'true') {
            return;
        }

        let events = [];

        try {
            events = JSON.parse(eventsElement.textContent || '[]');
        } catch (error) {
            events = [];
        }

        const eventDates = new Set(
            events
                .map((event) => (event.start || '').slice(0, 10))
                .filter(Boolean),
        );

        const navigateToDate = (dateString) => {
            const baseUrl = shell.dataset.traceabilityCalendarDetailsUrl;

            if (!baseUrl || !dateString) {
                return;
            }

            const url = new URL(baseUrl, window.location.href);
            url.searchParams.set('date', dateString);
            url.searchParams.set('month', dateString.slice(0, 7));
            url.searchParams.delete('from');
            url.searchParams.delete('to');
            window.location.assign(url.toString());
        };

        const calendar = new Calendar(calendarElement, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            initialDate: shell.dataset.traceabilityCalendarInitialDate || undefined,
            timeZone: 'local',
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
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                },
                timeGridDay: {
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                },
            },
            height: 'auto',
            expandRows: true,
            nowIndicator: true,
            navLinks: false,
            selectable: true,
            selectMirror: true,
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
            dayCellDidMount(info) {
                if (info.view.type !== 'dayGridMonth' || info.isOther) {
                    return;
                }

                const dateString = toLocalDateString(info.date);

                if (eventDates.has(dateString)) {
                    return;
                }

                const dayFrame = info.el.querySelector('.fc-daygrid-day-frame');

                if (!dayFrame) {
                    return;
                }

                const emptyState = document.createElement('div');
                emptyState.className = 'traceability-calendar-no-records';
                emptyState.textContent = 'No records';
                dayFrame.appendChild(emptyState);
            },
            dateClick(info) {
                navigateToDate(info.dateStr.slice(0, 10));
            },
            select(info) {
                const startDate = info.startStr.slice(0, 10);
                let endDate = info.endStr.slice(0, 10);

                if (info.allDay) {
                    const exclusiveEnd = new Date(info.endStr.slice(0, 10) + 'T00:00:00');
                    exclusiveEnd.setDate(exclusiveEnd.getDate() - 1);
                    endDate = toLocalDateString(exclusiveEnd);
                }

                setTraceabilityDateInput(fromInput, startDate);
                setTraceabilityDateInput(toInput, endDate);

                if (monthInput) {
                    monthInput.value = startDate.slice(0, 7);
                }

                rangeForm?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            },
            datesSet(info) {
                if (monthInput && !fromInput?.value && !toInput?.value) {
                    monthInput.value = toLocalDateString(info.view.currentStart).slice(0, 7);
                }
            },
            eventClick(info) {
                info.jsEvent.preventDefault();

                const baseUrl = shell.dataset.traceabilityCalendarDetailsUrl;
                const dateString = info.event.startStr.slice(0, 10);

                if (!baseUrl || !dateString) {
                    return;
                }

                const url = new URL(baseUrl, window.location.href);
                url.searchParams.set('date', dateString);
                url.searchParams.set('month', dateString.slice(0, 7));
                window.location.assign(url.toString());
            },
            eventDidMount(info) {
                info.el.classList.add('reservation-calendar__event');
            },
        });

        calendar.render();
        calendarElement.dataset.traceabilityCalendarInitialized = 'true';
    });
};
