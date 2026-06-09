<?php
$user    = current_user();
$current = trim($_GET['url'] ?? 'dashboard', '/');
?>

<!-- Móvil: menú lateral (solo visible al pulsar ☰) -->
<div class="offcanvas offcanvas-start app-sidebar-offcanvas d-lg-none"
     tabindex="-1"
     id="appSidebarOffcanvas"
     aria-labelledby="appSidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-white" id="appSidebarOffcanvasLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <?php require APP_PATH . '/Views/partials/sidebar-inner.php'; ?>
    </div>
</div>

<!-- Desktop: sidebar fijo -->
<aside class="app-sidebar d-none d-lg-flex">
    <?php require APP_PATH . '/Views/partials/sidebar-inner.php'; ?>
</aside>
