<?php $__env->startSection('title', 'Alerts'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $badge = [
        'expired' => 'text-bg-danger',
        'near_expiry' => 'text-bg-warning',
        'low_stock' => 'text-bg-info',
        'predicted_spoilage' => 'text-bg-secondary',
        'reorder_suggested' => 'text-bg-primary',
    ];
    $label = [
        'expired' => 'Expired',
        'near_expiry' => 'Near expiry',
        'low_stock' => 'Low stock',
        'predicted_spoilage' => 'Predicted spoilage',
        'reorder_suggested' => 'Reorder suggested',
    ];
    $aiTypes = ['predicted_spoilage', 'reorder_suggested'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Alerts</h1>
    <form method="POST" action="<?php echo e(route('alerts.run')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-primary">Run check</button>
    </form>
</div>

<div class="table-responsive mb-4">
    <table class="table table-bordered bg-white align-middle">
        <thead class="table-light">
            <tr><th>Type</th><th>Product</th><th>Batch</th><th>Message</th><th>Raised</th><th></th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <span class="badge <?php echo e($badge[$alert->alert_type] ?? 'text-bg-light'); ?>"><?php echo e($label[$alert->alert_type] ?? $alert->alert_type); ?></span>
                        <?php if(in_array($alert->alert_type, $aiTypes)): ?>
                            <span class="badge text-bg-dark">AI</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($alert->product->product_name); ?></td>
                    <td><?php echo e($alert->batch->batch_number ?? '-'); ?></td>
                    <td><?php echo e($alert->message); ?></td>
                    <td><?php echo e($alert->created_at->diffForHumans()); ?></td>
                    <td>
                        <form method="POST" action="<?php echo e(route('alerts.resolve', $alert)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark resolved</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted">No open alerts. Press "Run check".</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="h5">AI demand forecast <span class="badge text-bg-dark">AI</span></h2>
<p class="text-muted small">
    Next 7 days, per product. Model: exponential smoothing with a day-of-week pattern, tested on the last 7 days of usage.
    Confidence is the accuracy of that test.
</p>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Product</th><th>Expected demand</th><th>Predicted spoilage</th>
                <th>Suggested reorder</th><th>Confidence</th><th>Generated</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $forecasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($f->product->product_name); ?></td>
                    <td><?php echo e($f->predicted_demand_qty); ?> <?php echo e($f->product->unit_of_measure); ?></td>
                    <td><?php echo e($f->predicted_spoilage_qty); ?> <?php echo e($f->product->unit_of_measure); ?></td>
                    <td><?php echo e($f->recommended_reorder_qty); ?> <?php echo e($f->product->unit_of_measure); ?></td>
                    <td><?php echo e($f->confidence_score !== null ? $f->confidence_score.'%' : '-'); ?></td>
                    <td><?php echo e($f->generated_at->diffForHumans()); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted">
                    No forecasts yet. Make sure the forecast service is running and usage history exists, then press "Run check".
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/alerts/index.blade.php ENDPATH**/ ?>