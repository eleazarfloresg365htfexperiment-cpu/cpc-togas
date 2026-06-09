import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendario-alquileres');

    if (!calendarEl) {
        return;
    }

    const eventosUrl = calendarEl.dataset.eventosUrl;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        buttonText: {
            today: 'Mes actual',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },

        displayEventTime: true,
        eventDisplay: 'block',
        nowIndicator: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '20:00:00',
        slotDuration: '00:30:00',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },

        events: eventosUrl,

        eventClick: function (info) {
            info.jsEvent.preventDefault();

            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },

        eventDidMount: function (info) {
            const props = info.event.extendedProps;

            let tooltip = '';

            tooltip += `Recibo: ${props.codigo_recibo ?? 'N/A'}\n`;
            tooltip += `Cliente: ${props.cliente ?? 'N/A'}\n`;
            tooltip += `Estado alquiler: ${props.estado ?? 'N/A'}\n`;
            tooltip += `Estado pago: ${props.estado_pago ?? 'N/A'}\n`;

            if (props.fecha_entrega) {
                tooltip += `Entrega: ${props.fecha_entrega}`;

                if (props.hora_entrega) {
                    tooltip += ` ${props.hora_entrega}`;
                }

                tooltip += `\n`;
            }

            if (props.fecha_devolucion_programada) {
                tooltip += `Devolución: ${props.fecha_devolucion_programada}`;

                if (props.hora_devolucion_programada) {
                    tooltip += ` ${props.hora_devolucion_programada}`;
                }

                tooltip += `\n`;
            }

            if (props.saldo_pendiente !== null && props.saldo_pendiente !== undefined) {
                tooltip += `Saldo pendiente: Q${Number(props.saldo_pendiente).toFixed(2)}\n`;
            }

            info.el.setAttribute('title', tooltip);
        }
    });

    calendar.render();
});