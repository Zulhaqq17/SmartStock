<?php
/** Set $pageTitle before including. Closes with footer.php */
$pageTitle = $pageTitle ?? 'SmartStock';
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | SmartStock</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <a class="brand" href="/">SmartStock</a>
  <?php if (is_logged_in()): ?>
    <a href="/dashboard/">Dashboard</a>
    <a href="/inventory/">Inventory</a>
    <a href="/stock_entry/">Stock Entry</a>
    <a href="/stock_movement/">Movements</a>
    <a href="/alerts/">Alerts</a>
    <span class="spacer"></span>
    <span class="user"><?= e($_SESSION['full_name'] ?? '') ?> (<?= e(current_role()) ?>)</span>
    <a href="/auth/logout.php">Logout</a>
  <?php endif; ?>
</nav>
<main class="container">
<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
