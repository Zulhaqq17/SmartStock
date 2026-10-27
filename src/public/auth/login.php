<?php
require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../modules/auth/AuthService.php';

// TODO (Module 1): on POST -> csrf_verify(); attemptLogin($_POST['email'], $_POST['password']);
// success -> redirect('/dashboard/'); failure -> flash_set('error', 'Invalid email or password');

$pageTitle = 'Login';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Login</h1>
<p class="todo">TODO (Module 1): build the login form (email, password, csrf_field() inside the form).
Until this works, use <a href="/dev_login.php">/dev_login.php</a> to test as any role.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
