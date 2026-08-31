import * as bootstrap from 'bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';

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
        typeCell.textContent = item.type ?? 'â€”';

        const nameCell = document.createElement('td');
        const itemName = document.createElement('div');
        itemName.className = 'fw-semibold text-dark';
        itemName.textContent = item.name ?? 'â€”';
        const itemCode = document.createElement('div');
        itemCode.className = 'small text-secondary';
        itemCode.textContent = item.code ?? 'â€”';
        nameCell.append(itemName, itemCode);

        const quantityCell = document.createElement('td');
        quantityCell.textContent = item.quantity ?? 'â€”';

        const unitCell = document.createElement('td');
        unitCell.textContent = item.unit ?? 'â€”';

        const remarksCell = document.createElement('td');
        remarksCell.textContent = item.remarks ?? 'â€”';

        row.append(typeCell, nameCell, quantityCell, unitCell, remarksCell);
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
                const entryType = props.event_type === 'borrow' ? 'Borrow request' : 'Reservation';
                const referenceNo = props.reference_no ?? props.reservation_no ?? entryType;
                const marker = props.schedule_marker ? ` (${props.schedule_marker})` : '';
                const title = `${entryType} - ${referenceNo} - ${props.laboratory_name ?? 'Laboratory'}${marker}`;

                setTextContent(modalSelectors.title, title, 'Schedule details');
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
                    modalSelectors.badge.className = `badge ${statusBadgeClass(props.status, props.event_type)}`;
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
};
