<?php
$calId = 'cal-' . uniqid();
$calClienteId = isset($cliente) && is_array($cliente) ? (int) ($cliente['id'] ?? 0) : 0;
$isMobile = true; // se ajusta en JS
?>
<div id="<?= $calId ?>" data-cliente-id="<?= $calClienteId ?>"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('<?= $calId ?>');
    if (!el || typeof FullCalendar === 'undefined') return;

    var clienteId = el.dataset.clienteId;
    var apiUrl    = '<?= url('api/calendario/eventos') ?>';
    if (clienteId !== '0') apiUrl += '?cliente_id=' + clienteId;

    var mobile = window.matchMedia('(max-width: 991.98px)').matches;

    var cal = new FullCalendar.Calendar(el, {
        initialView: mobile ? 'listWeek' : 'dayGridMonth',
        locale: 'es',
        height: mobile ? 'auto' : (clienteId !== '0' ? 540 : 660),
        headerToolbar: mobile ? {
            left: 'prev,next',
            center: 'title',
            right: 'listWeek,dayGridMonth'
        } : {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Lista' },
        events: apiUrl,
        eventDidMount: function (info) {
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
