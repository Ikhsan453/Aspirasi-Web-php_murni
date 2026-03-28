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

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h4 class="text-white fw-bold mb-1"><i class="fas fa-user-edit me-2"></i>Edit Siswa</h4>
        <small class="text-muted-custom">Ubah data siswa NIS: <?= e($siswa['nis']) ?></small>
    </div>
    <a href="<?= url('admin/siswa/index.php') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-7">
        <div class="card slide-in">
            <div class="card-header">
                <h5 class="mb-0 text-white fw-semibold"><i class="fas fa-users me-2"></i>Form Edit Siswa</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                </div>
                <?php endif; ?>

                <div class="mb-4 p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.3);">
                    <small class="text-muted-custom">NIS (tidak dapat diubah)</small>
                    <div class="text-white fw-bold fs-5"><?= e($siswa['nis']) ?></div>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">
                                Kelas <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kelas" class="form-control form-control-lg"
                                   maxlength="10"
                                   value="<?= e($_POST['kelas'] ?? $siswa['kelas']) ?>"
                                   required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">
                                Jurusan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="jurusan" class="form-control form-control-lg"
                                   maxlength="100"
                                   value="<?= e($_POST['jurusan'] ?? $siswa['jurusan']) ?>"
                                   required>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-save me-2"></i>Update Data Siswa
                        </button>
                        <a href="<?= url('admin/siswa/index.php') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
