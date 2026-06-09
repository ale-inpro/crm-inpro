document.addEventListener('DOMContentLoaded', function () {
    var isDesktop = window.matchMedia('(min-width: 992px)').matches;

    // DataTables solo en desktop (tablas ocultas en móvil)
    if (typeof DataTable !== 'undefined' && isDesktop) {
      document.querySelectorAll('table.datatable').forEach(function (el) {
        if (!el.offsetParent && !el.closest('.d-lg-block')) return;
        var opts = {
          language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
          pageLength: 25,
          order: [],
        };
        if (el.classList.contains('datatable-clientes')) {
          opts.columnDefs = [{ orderable: false, searchable: false, targets: -1 }];
        }
        new DataTable(el, opts);
      });
    }

    // Tooltips Bootstrap
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      new bootstrap.Tooltip(el);
    });

    // Cerrar offcanvas al pulsar un enlace (móvil)
    document.querySelectorAll('#appSidebarOffcanvas a, #appMoreOffcanvas a').forEach(function (link) {
      link.addEventListener('click', function () {
        var sidebar = document.getElementById('appSidebarOffcanvas');
        var more = document.getElementById('appMoreOffcanvas');
        if (sidebar) {
          var inst = bootstrap.Offcanvas.getInstance(sidebar);
          if (inst) inst.hide();
        }
        if (more) {
          var instMore = bootstrap.Offcanvas.getInstance(more);
          if (instMore) instMore.hide();
        }
      });
    });
});
