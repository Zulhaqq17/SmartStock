<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/stock_movement/StockMovementService.php';
require_login();
$pageTitle = 'Record Movement';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Record Movement</h1>
<p class="todo">TODO: Module 4: form (product -> batch dropdown, type, quantity, remarks) -> recordMovement().</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
