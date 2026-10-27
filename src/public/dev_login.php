<?php
/**
 * DEV ONLY. Lets you test pages as any role before Module 1 (login) is finished.
 * Works only when APP_ENV=development. Delete this file before final submission.
 */
require __DIR__ . '/../includes/bootstrap.php';

if ((getenv('APP_ENV') ?: 'production') !== 'development') {
    http_response_code(404);
    exit('Not found');
}

$roles = [ROLE_MANAGER, ROLE_WAREHOUSE, ROLE_PRODUCTION];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    if (in_array($role, $roles, true)) {
        $stmt = db()->prepare(
            'SELECT u.user_id, u.full_name FROM users u JOIN roles r ON r.role_id = u.role_id
             WHERE r.role_name = ? ORDER BY u.user_id LIMIT 1'
        );
        $stmt->execute([$role]);
        if ($u = $stmt->fetch()) {
            $_SESSION['user_id']   = (int)$u['user_id'];
            $_SESSION['full_name'] = $u['full_name'];
            $_SESSION['role']      = $role;
            redirect('/dashboard/');
        }
        flash_set('error', 'No dev user for that role. Recreate the DB: docker compose down -v');
    }
}

$pageTitle = 'Dev Login';
require __DIR__ . '/../includes/header.php';
?>
<h1>Dev login</h1>
<p class="todo">Development only. Pick a role to test pages without real authentication.</p>
<form method="post">
  <select name="role">
    <?php foreach ($roles as $r): ?><option><?= e($r) ?></option><?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Log in as role</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
