<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$db  = getDB();
$nis = trim($_GET['nis'] ?? '');
$stmt = $db->prepare("SELECT * FROM tb_siswa WHERE nis = ?");
$stmt->execute([$nis]);
$siswa = $stmt->fetch();
if (!$siswa) { header('Location: ' . url('admin/siswa/index.php')); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $kelas   = trim($_POST['kelas'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    if (!$kelas)   $errors[] = 'Kelas wajib diisi.';
    if (!$jurusan) $errors[] = 'Jurusan wajib diisi.';

    if (empty($errors)) {
        $db->prepare("UPDATE tb_siswa SET kelas=?, jurusan=? WHERE nis=?")->execute([$kelas, $jurusan, $nis]);
        setFlash('success', 'Data siswa berhasil diupdate.');
        header('Location: ' . url('admin/siswa/index.php'));
        exit;
    }
}

$pageTitle = 'Edit Siswa';
require_once __DIR__ . '/../../includes/header_admin.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-user-edit me-2"></i>Edit Siswa</h4>
    <a href="<?= url('admin/siswa/index.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>
<div class="card" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;max-width:500px;">
    <div class="card-body p-4">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e) echo '<div>' . e($e) . '</div>'; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label">NIS</label>
                <input type="text" class="form-control" value="<?= e($siswa['nis']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                <input type="text" name="kelas" class="form-control" maxlength="10"
                       value="<?= e($_POST['kelas'] ?? $siswa['kelas']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                <input type="text" name="jurusan" class="form-control" maxlength="100"
                       value="<?= e($_POST['jurusan'] ?? $siswa['jurusan']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
