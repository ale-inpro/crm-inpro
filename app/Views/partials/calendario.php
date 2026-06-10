<?php
$calId = 'cal-' . uniqid();
$calClienteId = isset($cliente) && is_array($cliente) ? (int) ($cliente['id'] ?? 0) : 0;
$calAutoInit = $calAutoInit ?? true;
?>
<div class="inpro-calendario-wrap">
    <div id="<?= $calId ?>" class="inpro-calendario" data-cliente-id="<?= $calClienteId ?>"></div>
    <p class="text-muted small mt-2 mb-0 d-none inpro-calendario-empty">
        No hay visitas programadas ni tareas pendientes con fecha en este periodo.
    </p>
</div>
<script>
(function () {
    var calElId = <?= json_encode($calId) ?>;
    var autoInit = <?= $calAutoInit ? 'true' : 'false' ?>;

    function emptyEl(calEl) {
        var wrap = calEl.closest('.inpro-calendario-wrap');
        return wrap ? wrap.querySelector('.inpro-calendario-empty') : null;
    }

    // Observa el contenedor: cada vez que pasa a tener ancho real (al mostrarse
    // la pestaña, cambiar viewport, colapsar sidebar...), reajusta el calendario.
    // Es la forma nativa y fiable de evitar el grid colapsado por width=0.
    function attachResizeObserver(el) {
        if (el._fcResizeObserver || typeof ResizeObserver === 'undefined') return;
        var lastWidth = 0;
        var raf = null;
        var ro = new ResizeObserver(function (entries) {
            var width = entries[0] ? entries[0].contentRect.width : el.offsetWidth;
            if (width > 20 && Math.abs(width - lastWidth) > 1) {
                lastWidth = width;
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(function () {
                    if (el._fcCalendar) el._fcCalendar.updateSize();
                });
            }
        });
        ro.observe(el);
        el._fcResizeObserver = ro;
    }

    function buildCalendar(el) {
        if (!el || typeof FullCalendar === 'undefined') return null;
        if (el.dataset.calInited === '1' && el._fcCalendar) {
            el._fcCalendar.updateSize();
            return el._fcCalendar;
        }

        var clienteId = el.dataset.clienteId || '0';
        var apiUrl = <?= json_encode(url('api/calendario/eventos')) ?>;
        if (clienteId !== '0') apiUrl += '?cliente_id=' + clienteId;

        var mobile = window.matchMedia('(max-width: 991.98px)').matches;
        var empty  = emptyEl(el);

        var cal = new FullCalendar.Calendar(el, {
            initialView: mobile ? 'listWeek' : 'dayGridMonth',
            locale: 'es',
            height: mobile ? 'auto' : (clienteId !== '0' ? 540 : 660),
            headerToolbar: mobile
                ? { left: 'prev,next', center: 'title', right: 'listWeek,dayGridMonth' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
            buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Lista' },
            events: function (_info, ok, fail) {
                fetch(apiUrl, { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var list = Array.isArray(data) ? data : [];
                        if (empty) empty.classList.toggle('d-none', list.length > 0);
                        ok(list);
                    })
                    .catch(function () {
                        if (empty) empty.classList.remove('d-none');
                        fail();
                    });
            },
            eventDidMount: function (info) {
                var p = info.event.extendedProps;
                var lines = [];
                if (p.cliente)   lines.push(p.cliente);
                if (p.descripcion) lines.push(p.descripcion);
                if (p.asignado)  lines.push('Asignado: ' + p.asignado);
                if (p.prioridad) lines.push('Prioridad: ' + p.prioridad);
                if (lines.length) { info.el.title = lines.join('\n'); info.el.style.cursor = 'pointer'; }
                if (p.destacada) info.el.insertAdjacentHTML('afterbegin', '<span style="margin-right:2px">⭐</span>');
            },
            eventClick: function (info) {
                if (info.event.url) { info.jsEvent.preventDefault(); location.href = info.event.url; }
            }
        });

        cal.render();
        el.dataset.calInited = '1';
        el._fcCalendar = cal;
        attachResizeObserver(el);
        // Reajuste tras el primer paint (por si se construyó durante el fade)
        requestAnimationFrame(function () { cal.updateSize(); });
        return cal;
    }

    // --- API global ---

    window.initInproCalendario = function (el) {
        if (typeof el === 'string') el = document.getElementById(el);
        return buildCalendar(el);
    };

    window.resizeInproCalendarios = function () {
        document.querySelectorAll('.inpro-calendario[data-cal-inited="1"]').forEach(function (el) {
            if (el._fcCalendar) el._fcCalendar.updateSize();
        });
    };

    if (autoInit) {
        document.addEventListener('DOMContentLoaded', function () {
            buildCalendar(document.getElementById(calElId));
        });
    }
})();
</script>
