<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

//INISIALISASI DATABASE & AMBIL DATA
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM tb_kategori WHERE id_kategori = ?");
$stmt->execute([$id]);
$kategori = $stmt->fetch();
//VALIDASI DATA EXIST
if (!$kategori) { header('Location: ' . url('admin/kategori/index.php')); exit; }

//TANGANI POST REQUEST
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //VALIDASI CSRF
    verifyCsrf();
    //AMBIL & SANITASI DATA
    $ket = trim($_POST['ket_kategori'] ?? '');
    //VALIDASI INPUT
    if (!$ket) $errors[] = 'Nama kategori wajib diisi.';
    elseif (mb_strlen($ket) > 30) $errors[] = 'Nama kategori maksimal 30 karakter.';

    //UPDATE DATABASE
    if (empty($errors)) {
        $db->prepare("UPDATE tb_kategori SET ket_kategori=? WHERE id_kategori=?")->execute([$ket, $id]);
        setFlash('success', 'Kategori berhasil diupdate.');
        header('Location: ' . url('admin/kategori/index.php'));
        exit;
    }
}

$pageTitle = 'Edit Kategori';
require_once __DIR__ . '/../../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h4 class="text-white fw-bold mb-1"><i class="fas fa-edit me-2"></i>Edit Kategori</h4>
        <small class="text-muted-custom">Ubah nama kategori aspirasi</small>
    </div>
    <a href="<?= url('admin/kategori/index.php') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card slide-in">
            <div class="card-header">
                <h5 class="mb-0 text-white fw-semibold"><i class="fas fa-tags me-2"></i>Form Edit Kategori</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                </div>
                <?php endif; ?>

                <div class="mb-3 p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.3);">
                    <small class="text-muted-custom">ID Kategori</small>
                    <div class="text-white fw-semibold">#<?= $kategori['id_kategori'] ?></div>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="ket_kategori" class="form-control form-control-lg"
                               maxlength="30"
                               value="<?= e($_POST['ket_kategori'] ?? $kategori['ket_kategori']) ?>"
                               required autofocus>
                        <div class="form-text text-muted mt-1">Maksimal 30 karakter</div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-save me-2"></i>Update Kategori
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
