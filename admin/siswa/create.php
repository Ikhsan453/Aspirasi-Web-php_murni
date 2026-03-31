<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

//OPSI DROPDOWN
$kelas_options = ['X', 'XI', 'XII'];
$jurusan_options = [
    'Teknik Elektronika',
    'Teknik Kimia Industri',
    'Kimia Analisis',
    'Teknik Ketenagalistrikan',
    'Teknik Otomotif',
    'Teknik Mesin',
    'Pengelasan dan Fabrikasi Logam',
    'Teknik Pengembangan Perangkat Lunak dan Gim',
    'Teknologi Farmasi'
];

//TANGANI POST REQUEST
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //VALIDASI CSRF
    verifyCsrf();
    //AMBIL & SANITASI DATA
    $nis     = trim($_POST['nis'] ?? '');
    $kelas   = trim($_POST['kelas'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');

    //VALIDASI FORM
    if (!$nis)     $errors[] = 'NIS wajib diisi.';
    elseif (mb_strlen($nis) > 10) $errors[] = 'NIS maksimal 10 karakter.';
    if (!$kelas)   $errors[] = 'Kelas wajib diisi.';
    if (!$jurusan) $errors[] = 'Jurusan wajib diisi.';

    //CEK DUPLIKASI NIS
    if (empty($errors)) {
        $cek = getDB()->prepare("SELECT nis FROM tb_siswa WHERE nis = ?");
        $cek->execute([$nis]);
        if ($cek->fetch()) $errors[] = 'NIS sudah terdaftar.';
    }

    //INSERT KE DATABASE
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

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h4 class="text-white fw-bold mb-1"><i class="fas fa-user-plus me-2"></i>Tambah Siswa</h4>
        <small class="text-muted-custom">Daftarkan siswa baru ke sistem</small>
    </div>
    <a href="<?= url('admin/siswa/index.php') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-7">
        <div class="card slide-in">
            <div class="card-header">
                <h5 class="mb-0 text-white fw-semibold"><i class="fas fa-users me-2"></i>Form Data Siswa</h5>
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
                            NIS <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nis" class="form-control form-control-lg"
                               maxlength="10" value="<?= e($_POST['nis'] ?? '') ?>"
                               placeholder="Masukkan NIS siswa" required autofocus>
                        <div class="form-text text-muted mt-1">Nomor Induk Siswa, maksimal 10 karakter</div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">
                                Kelas <span class="text-danger">*</span>
                            </label>
                            <select name="kelas" class="form-select form-select-lg" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelas_options as $k): ?>
                                <option value="<?= $k ?>" <?= ($_POST['kelas'] ?? '') === $k ? 'selected' : '' ?>>Kelas <?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">
                                Jurusan <span class="text-danger">*</span>
                            </label>
                            <select name="jurusan" class="form-select form-select-lg" required>
                                <option value="">-- Pilih Jurusan --</option>
                                <?php foreach ($jurusan_options as $j): ?>
                                <option value="<?= $j ?>" <?= ($_POST['jurusan'] ?? '') === $j ? 'selected' : '' ?>><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-save me-2"></i>Simpan Data Siswa
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
