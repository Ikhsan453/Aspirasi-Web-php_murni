<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$admin        = getAdminUser();
$pageTitle    = $pageTitle ?? 'Admin - Aspirasi Web';
$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
$currentPath  = $_SERVER['PHP_SELF'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/custom.css') ?>" rel="stylesheet">
    <?= $extraStyles ?? '' ?>
    <style>
        /* Fixed navbar */
        .admin-navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 64px;
            z-index: 1040;
            background: var(--gradient-primary);
            border-bottom: 1px solid var(--secondary-blue);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }
        /* Sidebar fixed below navbar */
        .admin-sidebar {
            position: fixed;
            top: 64px; left: 0; bottom: 0;
            width: 250px;
            background: var(--gradient-primary);
            border-right: 1px solid var(--secondary-blue);
            overflow-y: auto;
            z-index: 1030;
            padding: 1.5rem 1rem;
            transition: left 0.3s ease;
        }
        .admin-sidebar .nav-link {
            color: var(--text-muted);
            padding: 0.7rem 1rem;
            border-radius: 8px;
            margin: 0.15rem 0;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: var(--gradient-accent);
            color: #fff;
            transform: translateX(4px);
        }
        .admin-sidebar .nav-link i { width: 22px; text-align: center; }
        /* Main content */
        .admin-main {
            margin-left: 250px;
            margin-top: 64px;
            min-height: calc(100vh - 64px);
            padding: 2rem;
        }
        /* Sidebar overlay mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 64px; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1029;
        }
        .sidebar-overlay.show { display: block; }
        @media (max-width: 767.98px) {
            .admin-sidebar { left: -250px; }
            .admin-sidebar.show { left: 0; }
            .admin-main { margin-left: 0; padding: 1rem; }
        }
        @media (min-width: 768px) and (max-width: 991.98px) {
            .admin-sidebar { width: 200px; }
            .admin-main { margin-left: 200px; }
        }
    </style>
</head>
<body style="background:var(--gradient-primary);padding-top:0;">

<!-- Navbar -->
<nav class="admin-navbar">
    <a href="<?= url('admin/dashboard.php') ?>" class="text-white text-decoration-none d-flex align-items-center me-auto fw-bold fs-5">
        <i class="fas fa-shield-alt me-2" style="color:var(--light-blue)"></i>
        Admin Panel
    </a>
    <button class="btn btn-sm d-md-none me-2" style="background:transparent;border:1px solid rgba(255,255,255,0.3);color:#fff;" onclick="toggleAdminSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dropdown">
        <a href="#" class="text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle fs-5"></i>
            <span class="d-none d-md-inline"><?= e($admin['username']) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= url() ?>"><i class="fas fa-home me-2"></i>Lihat Website</a></li>
            <li><hr class="dropdown-divider" style="border-color:var(--secondary-blue)"></li>
            <li>
                <a class="dropdown-item" href="#" onclick="document.getElementById('logoutForm').submit(); return false;">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Logout form (hidden) -->
<form id="logoutForm" action="<?= url('admin/logout.php') ?>" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
</form>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>

<!-- Sidebar -->
<nav class="admin-sidebar" id="adminSidebar">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
             style="width:56px;height:56px;background:var(--gradient-accent);">
            <i class="fas fa-user-shield text-white fs-4"></i>
        </div>
        <div class="text-white fw-semibold"><?= e($admin['username']) ?></div>
        <small style="color:var(--text-muted)"><?= e($admin['role'] ?? 'admin') ?></small>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= str_contains($currentPath, 'dashboard') ? 'active' : '' ?>"
               href="<?= url('admin/dashboard.php') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= str_contains($currentPath, '/admin/kategori') ? 'active' : '' ?>"
               href="<?= url('admin/kategori/index.php') ?>">
                <i class="fas fa-tags me-2"></i>Kategori
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= str_contains($currentPath, '/admin/siswa') ? 'active' : '' ?>"
               href="<?= url('admin/siswa/index.php') ?>">
                <i class="fas fa-users me-2"></i>Data Siswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= str_contains($currentPath, '/admin/aspirasi') ? 'active' : '' ?>"
               href="<?= url('admin/aspirasi/index.php') ?>">
                <i class="fas fa-comments me-2"></i>Aspirasi
            </a>
        </li>
    </ul>

    <hr style="border-color:rgba(51,65,85,0.5);margin:1rem 0">

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="<?= url() ?>">
                <i class="fas fa-external-link-alt me-2"></i>Lihat Website
            </a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<main class="admin-main">
<?php if ($flashSuccess): ?>
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= e($flashSuccess) ?>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?= e($flashError) ?>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
