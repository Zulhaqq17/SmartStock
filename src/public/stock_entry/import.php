<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/stock_entry/StockEntryService.php';
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);
$pageTitle = 'Import CSV';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Import CSV</h1>
<p class="todo">TODO: Module 3: CSV upload form -> importFromCsv(); show per-row errors. Document the CSV columns.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
