<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/inventory/InventoryService.php';
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);
$pageTitle = 'Edit Product';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Edit Product</h1>
<p class="todo">TODO: Module 2: load product by ?id=, form + updateProduct().</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
