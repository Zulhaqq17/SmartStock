<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/stock_movement/StockMovementService.php';
require_login();
$pageTitle = 'Stock Movements';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Stock Movements</h1>
<p class="todo">TODO: Module 4: transaction history table with filters -> getMovementHistory().</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
