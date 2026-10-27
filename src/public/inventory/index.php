<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/inventory/InventoryService.php';
require_login();   // all roles may view the product list

$products = getAllProducts();   // logic lives in modules/, page only displays it
$pageTitle = 'Inventory';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Inventory</h1>
<p class="todo">REFERENCE PAGE (working). Module 2 owner: add search, category filter, and Add/Edit buttons (Manager/Warehouse only).</p>
<table>
  <tr><th>SKU</th><th>Product</th><th>Category</th><th>Total stock</th><th>Min level</th></tr>
  <?php foreach ($products as $p): ?>
  <tr>
    <td><?= e($p['sku_code']) ?></td>
    <td><?= e($p['product_name']) ?></td>
    <td><?= e($p['category_name']) ?></td>
    <td><?= e($p['total_stock']) ?> <?= e($p['unit_of_measure']) ?></td>
    <td><?= e($p['min_stock_level']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
