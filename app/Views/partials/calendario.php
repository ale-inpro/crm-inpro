<?php
$calId = 'cal-' . uniqid();
$calClienteId = isset($cliente) && is_array($cliente) ? (int) ($cliente['id'] ?? 0) : 0;
?>
<div id="<?= $calId ?>" data-cliente-id="<?= $calClienteId ?>"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('<?= $calId ?>');
    if (!el || typeof FullCalendar === 'undefined') return;

    var clienteId = el.dataset.clienteId;
    var apiUrl    = '<?= url('api/calendario/eventos') ?>';
    if (clienteId !== '0') apiUrl += '?cliente_id=' + clienteId;

    var cal = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: clienteId !== '0' ? 540 : 660,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Lista' },
        events: apiUrl,
        eventDidMount: function (info) {
            // Tooltip con descripción al hacer hover
            var props = info.event.extendedProps;
            var desc  = props.descripcion || '';
            var lines = [];
            if (props.cliente) lines.push('📍 ' + props.cliente);
            if (desc)          lines.push(desc);
            if (props.asignado) lines.push('👤 ' + props.asignado);
            if (props.prioridad) lines.push('⚑ ' + props.prioridad);
            if (lines.length) {
                info.el.title = lines.join('\n');
                info.el.style.cursor = 'pointer';
            }
            // Estrella en eventos destacados
            if (props.destacada) {
                info.el.insertAdjacentHTML('afterbegin', '<span style="margin-right:2px;opacity:.9">⭐</span>');
            }
        },
        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        }
    });
    cal.render();
});
</script>
