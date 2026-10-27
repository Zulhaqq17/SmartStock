<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/dashboard/DashboardService.php';
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);
$pageTitle = 'Reports';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Reports</h1>
<p class="todo">TODO: Module 6: basic inventory report table + filters (+ CSV export if time).</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
