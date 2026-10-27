<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/stock_entry/StockEntryService.php';
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);
$pageTitle = 'Stock Entry';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Stock Entry</h1>
<p class="todo">TODO: Module 3: list recent batches with links to Add Batch and Import CSV.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
