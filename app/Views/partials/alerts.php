<?php
$flashSuccess = flash('success');
$flashError   = flash('error');
?>
<?php if ($flashSuccess || $flashError): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($flashSuccess): ?>
    showToast(<?= json_encode($flashSuccess) ?>, 'success');
    <?php endif; ?>
    <?php if ($flashError): ?>
    showToast(<?= json_encode($flashError) ?>, 'error');
    <?php endif; ?>
});
</script>
<?php endif; ?>
