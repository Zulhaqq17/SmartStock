<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/alerts/AlertService.php';
require_login();
$pageTitle = 'Alerts';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Alerts</h1>
<p class="todo">TODO: Module 5: 'Run check' button -> runAllChecks(); table of getOpenAlerts(); 'Mark resolved' -> resolveAlert(). Show expired/near-expiry/low-stock with the .badge-* CSS classes.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
