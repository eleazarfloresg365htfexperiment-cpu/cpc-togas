import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendario-alquileres');

    if (!calendarEl) {
        return;
    }

    const eventosUrl = calendarEl.dataset.eventosUrl;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, listPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,listDay'
        },

        buttonText: {
            today: 'Mes actual',
            month: 'Mes',
            week: 'Semana',
            list: 'Día'
        },

        views: {
            listDay: {
                buttonText: 'Día',
                noEventsText: 'No hay actividades programadas para este día.'
            }
        },

        displayEventTime: false,
        eventDisplay: 'block',
        nowIndicator: false,
        lazyFetching: false,

        events: function (fetchInfo, successCallback, failureCallback) {
            const url = new URL(eventosUrl, window.location.origin);

            const startDate = new Date(fetchInfo.startStr);
            const endDate = new Date(fetchInfo.endStr);
            const diferenciaDias = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));

            const vista = diferenciaDias <= 1 ? 'listDay' : 'dayGridMonth';

            url.searchParams.set('start', fetchInfo.startStr);
            url.searchParams.set('end', fetchInfo.endStr);
            url.searchParams.set('vista', vista);

            console.log('Vista enviada al backend:', vista);
            console.log('URL enviada al backend:', url.toString());

            fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    console.log('Eventos recibidos:', data);
                    successCallback(data);
                })
                .catch(function (error) {
                    console.error('Error cargando eventos:', error);
                    failureCallback(error);
                });
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();

            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },

        eventDidMount: function (info) {
            const props = info.event.extendedProps;

            let tooltip = '';

            if (props.tipo_evento_texto) {
                tooltip += `Tipo: ${props.tipo_evento_texto}\n`;
            }

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

            if (props.fecha_limite_pago_final) {
                tooltip += `Límite de pago: ${props.fecha_limite_pago_final}\n`;
            }

            if (props.saldo_pendiente !== null && props.saldo_pendiente !== undefined) {
                tooltip += `Saldo pendiente: Q${Number(props.saldo_pendiente).toFixed(2)}\n`;
            }

            info.el.setAttribute('title', tooltip);
        }
    });

    calendar.render();
});