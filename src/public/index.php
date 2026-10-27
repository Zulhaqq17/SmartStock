<?php
require __DIR__ . '/../includes/bootstrap.php';
redirect(is_logged_in() ? '/dashboard/' : '/auth/login.php');
