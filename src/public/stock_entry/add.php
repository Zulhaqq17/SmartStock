<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/stock_entry/StockEntryService.php';
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);
$pageTitle = 'Add Batch';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Add Batch</h1>
<p class="todo">TODO: Module 3: manual batch form (product, batch number, quantity, manufacture/expiry date) -> addBatch().</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
