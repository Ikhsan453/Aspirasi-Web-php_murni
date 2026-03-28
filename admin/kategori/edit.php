<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM tb_kategori WHERE id_kategori = ?");
$stmt->execute([$id]);
$kategori = $stmt->fetch();
if (!$kategori) { header('Location: ' . url('admin/kategori/index.php')); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $ket = trim($_POST['ket_kategori'] ?? '');
    if (!$ket) $errors[] = 'Nama kategori wajib diisi.';
    elseif (mb_strlen($ket) > 30) $errors[] = 'Nama kategori maksimal 30 karakter.';

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
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-edit me-2"></i>Edit Kategori</h4>
    <a href="<?= url('admin/kategori/index.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>
<div class="card" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;max-width:500px;">
    <div class="card-body p-4">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e) echo '<div>' . e($e) . '</div>'; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="ket_kategori" class="form-control" maxlength="30"
                       value="<?= e($_POST['ket_kategori'] ?? $kategori['ket_kategori']) ?>" required autofocus>
                <small class="text-muted-custom">Maksimal 30 karakter</small>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
