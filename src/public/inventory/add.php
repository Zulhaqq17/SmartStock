<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/inventory/InventoryService.php';
require_role([ROLE_MANAGER, ROLE_WAREHOUSE]);
$pageTitle = 'Add Product';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Add Product</h1>
<p class="todo">TODO: Module 2: form + createProduct(). Use csrf_field()/csrf_verify() and validate input.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
