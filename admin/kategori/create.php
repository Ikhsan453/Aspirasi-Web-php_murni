<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $ket = trim($_POST['ket_kategori'] ?? '');
    if (!$ket) $errors[] = 'Nama kategori wajib diisi.';
    elseif (mb_strlen($ket) > 30) $errors[] = 'Nama kategori maksimal 30 karakter.';

    if (empty($errors)) {
        getDB()->prepare("INSERT INTO tb_kategori (ket_kategori) VALUES (?)")->execute([$ket]);
        setFlash('success', 'Kategori berhasil ditambahkan.');
        header('Location: ' . url('admin/kategori/index.php'));
        exit;
    }
}

$pageTitle = 'Tambah Kategori';
require_once __DIR__ . '/../../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h4 class="text-white fw-bold mb-1"><i class="fas fa-plus-circle me-2"></i>Tambah Kategori</h4>
        <small class="text-muted-custom">Tambah kategori aspirasi baru</small>
    </div>
    <a href="<?= url('admin/kategori/index.php') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card slide-in">
            <div class="card-header">
                <h5 class="mb-0 text-white fw-semibold"><i class="fas fa-tags me-2"></i>Form Kategori</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="ket_kategori" class="form-control form-control-lg"
                               maxlength="30" value="<?= e($_POST['ket_kategori'] ?? '') ?>"
                               placeholder="Contoh: Kerusakan Fasilitas" required autofocus>
                        <div class="form-text text-muted mt-1">Maksimal 30 karakter</div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-save me-2"></i>Simpan Kategori
                        </button>
                        <a href="<?= url('admin/kategori/index.php') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
