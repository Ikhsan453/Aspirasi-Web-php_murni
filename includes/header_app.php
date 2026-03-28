<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
$pageTitle    = $pageTitle ?? 'Sistem Aspirasi Web';
$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
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
</head>
<body class="fade-in">
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= url() ?>">
            <i class="fas fa-school me-2 text-light-blue"></i>
            <span class="fw-bold">Sistem Aspirasi Web</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url() ?>"><i class="fas fa-home me-1"></i> Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('aspirasi/create.php') ?>"><i class="fas fa-plus-circle me-1"></i> Buat Aspirasi</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('aspirasi/status.php') ?>"><i class="fas fa-search me-1"></i> Cek Status</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('admin/login.php') ?>"><i class="fas fa-shield-alt me-1"></i> Admin</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="py-4">
<?php if ($flashSuccess): ?>
<div class="container mb-4">
    <div class="alert alert-success alert-dismissible fade show slide-in" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= e($flashSuccess) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="container mb-4">
    <div class="alert alert-danger alert-dismissible fade show slide-in" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($flashError) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
