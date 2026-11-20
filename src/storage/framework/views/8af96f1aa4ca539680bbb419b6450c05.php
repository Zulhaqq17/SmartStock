<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'SmartStock'); ?> | SmartStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(url('/')); ?>">SmartStock</a>

        <?php if(auth()->guard()->check()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('inventory.*') ? 'active' : ''); ?>" href="<?php echo e(route('inventory.index')); ?>">Inventory</a></li>

                    <?php if(auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE)): ?>
                        <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('stock_entry.*') ? 'active' : ''); ?>" href="<?php echo e(route('stock_entry.index')); ?>">Stock Entry</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('stock_movement.*') ? 'active' : ''); ?>" href="<?php echo e(route('stock_movement.index')); ?>">Movements</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('alerts.*') ? 'active' : ''); ?>" href="<?php echo e(route('alerts.index')); ?>">Alerts</a></li>

                    <?php if(auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE)): ?>
                        <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('reports') ? 'active' : ''); ?>" href="<?php echo e(route('reports')); ?>">Reports</a></li>
                    <?php endif; ?>

                    <?php if(auth()->user()->hasRole(\App\Models\Role::MANAGER)): ?>
                        <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>" href="<?php echo e(route('users.index')); ?>">Users</a></li>
                    <?php endif; ?>
                </ul>

                <span class="navbar-text me-3 text-white-50">
                    <?php echo e(auth()->user()->name); ?> (<?php echo e(auth()->user()->role->role_name); ?>)
                </span>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</nav>

<main class="container my-4">
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</main>

<footer class="text-center text-muted small py-4">SmartStock &middot; BITU 3923 Group K27</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>