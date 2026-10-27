<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/dashboard/DashboardService.php';
require_login();
$pageTitle = 'Dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Dashboard</h1>
<p class="todo">TODO: Module 6: summary cards, low-stock list, expiring batches, recent movements. Call runAllChecks() on load (see modules/alerts).</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
