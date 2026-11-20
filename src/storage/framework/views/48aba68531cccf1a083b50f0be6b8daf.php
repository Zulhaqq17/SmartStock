<?php $__env->startSection('title', 'Inventory'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Inventory</h1>

    <?php if(auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE)): ?>
        <a href="<?php echo e(route('inventory.create')); ?>" class="btn btn-primary">Add product</a>
    <?php endif; ?>
</div>

<div class="alert alert-warning">
    <strong>REFERENCE PAGE (working).</strong> Module 2 owner: add search, a category filter and Edit buttons.
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>SKU</th><th>Product</th><th>Category</th><th>Total stock</th><th>Min level</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($product->sku_code); ?></td>
                    <td><?php echo e($product->product_name); ?></td>
                    <td><?php echo e($product->category->category_name); ?></td>
                    <td><?php echo e($product->total_stock ?? 0); ?> <?php echo e($product->unit_of_measure); ?></td>
                    <td><?php echo e($product->min_stock_level); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-muted">No products yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inventory/index.blade.php ENDPATH**/ ?>