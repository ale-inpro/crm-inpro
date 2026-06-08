document.addEventListener('DOMContentLoaded', function () {
    // DataTables en tablas con clase .datatable
    if (typeof DataTable !== 'undefined') {
      document.querySelectorAll('table.datatable').forEach(function (el) {
        new DataTable(el, {
          language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
          pageLength: 25,
          order: [],
        });
      });
    }
  
    // Tooltips Bootstrap
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
  });