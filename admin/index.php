<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: ' . url('admin/dashboard.php'));
} else {
    header('Location: ' . url('admin/login.php'));
}
exit;
