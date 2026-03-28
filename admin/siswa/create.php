<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nis     = trim($_POST['nis'] ?? '');
    $kelas   = trim($_POST['kelas'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');

    if (!$nis)     $errors[] = 'NIS wajib diisi.';
    elseif (mb_strlen($nis) > 10) $errors[] = 'NIS maksimal 10 karakter.';
    if (!$kelas)   $errors[] = 'Kelas wajib diisi.';
    if (!$jurusan) $errors[] = 'Jurusan wajib diisi.';

    if (empty($errors)) {
        $cek = getDB()->prepare("SELECT nis FROM tb_siswa WHERE nis = ?");
        $cek->execute([$nis]);
        if ($cek->fetch()) $errors[] = 'NIS sudah terdaftar.';
    }

    if (empty($errors)) {
        getDB()->prepare("INSERT INTO tb_siswa (nis,kelas,jurusan) VALUES (?,?,?)")->execute([$nis,$kelas,$jurusan]);
        setFlash('success', 'Siswa berhasil ditambahkan.');
        header('Location: ' . url('admin/siswa/index.php'));
        exit;
    }
}

$pageTitle = 'Tambah Siswa';
require_once __DIR__ . '/../../includes/header_admin.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-user-plus me-2"></i>Tambah Siswa</h4>
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
                <label class="form-label">NIS <span class="text-danger">*</span></label>
                <input type="text" name="nis" class="form-control" maxlength="10"
                       value="<?= e($_POST['nis'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                <input type="text" name="kelas" class="form-control" maxlength="10"
                       value="<?= e($_POST['kelas'] ?? '') ?>" placeholder="Contoh: XII" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                <input type="text" name="jurusan" class="form-control" maxlength="100"
                       value="<?= e($_POST['jurusan'] ?? '') ?>" placeholder="Contoh: IPA 1" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Simpan</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
