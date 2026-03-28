<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nis        = trim($_POST['nis'] ?? '');
    $idKategori = (int)($_POST['id_kategori'] ?? 0);
    $lokasi     = trim($_POST['lokasi'] ?? '');
    $ket        = trim($_POST['ket'] ?? '');

    if (!$nis)        $errors['nis']        = 'NIS wajib diisi.';
    if (!$idKategori) $errors['id_kategori'] = 'Kategori wajib dipilih.';
    if (!$lokasi)     $errors['lokasi']      = 'Lokasi wajib diisi.';
    if (!$ket)        $errors['ket']         = 'Keterangan wajib diisi.';

    if (empty($errors)) {
        $cek = $db->prepare("SELECT nis FROM tb_siswa WHERE nis = ?");
        $cek->execute([$nis]);
        if (!$cek->fetch()) $errors['nis'] = 'NIS tidak ditemukan. Silakan hubungi admin.';
    }

    if (empty($errors)) {
        $fotoName = null;
        if (!empty($_FILES['foto']['name'])) {
            $file    = $_FILES['foto'];
            $allowed = ['image/jpeg','image/png','image/jpg','image/gif'];
            if (!in_array($file['type'], $allowed)) {
                $errors['foto'] = 'Format file tidak valid. Gunakan JPG, PNG, atau GIF.';
            } elseif ($file['size'] > 2*1024*1024) {
                $errors['foto'] = 'Ukuran file terlalu besar. Maksimal 2MB.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/aspirasi/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $fotoName = time() . '_' . basename($file['name']);
                move_uploaded_file($file['tmp_name'], $uploadDir . $fotoName);
            }
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO tb_input_aspirasi (nis,id_kategori,lokasi,ket,foto) VALUES (?,?,?,?,?)");
        $stmt->execute([$nis, $idKategori, $lokasi, $ket, $fotoName]);
        $idPelaporan = $db->lastInsertId();

        // Ambil ket_kategori dulu
        $katStmt = $db->prepare("SELECT ket_kategori FROM tb_kategori WHERE id_kategori = ?");
        $katStmt->execute([$idKategori]);
        $ketKategori = $katStmt->fetchColumn();

        $db->prepare("INSERT INTO tb_aspirasi (id_pelaporan,id_kategori,ket_kategori,status) VALUES (?,?,?,'Menunggu')")
           ->execute([$idPelaporan, $idKategori, $ketKategori]);

        $db->prepare("INSERT INTO tb_aspirasi_status_history (id_pelaporan,status,feedback,changed_by) VALUES (?,'Menunggu','Aspirasi telah diterima dan sedang menunggu review dari admin.','system')")
           ->execute([$idPelaporan]);

        setFlash('success', 'Aspirasi berhasil dikirim!');
        $_SESSION['last_id_pelaporan'] = $idPelaporan;
        header('Location: ' . url('aspirasi/success.php'));
        exit;
    }
}

$kategoris = $db->query("SELECT * FROM tb_kategori ORDER BY id_kategori")->fetchAll();
$pageTitle  = 'Buat Aspirasi';
$extraStyles = '<style>
.create-header{background:var(--gradient-accent);border-radius:16px;padding:2rem;margin-bottom:2rem;color:white;position:relative;overflow:hidden;}
.create-header::before{content:"";position:absolute;top:-50%;right:-10%;width:300px;height:300px;background:rgba(255,255,255,.1);border-radius:50%;animation:float 6s ease-in-out infinite;}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
.form-card{background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:20px;}
.form-section{background:rgba(51,65,85,.3);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;border:1px solid rgba(100,116,139,.3);}
.form-section-title{color:var(--accent-blue);font-weight:700;font-size:1.1rem;margin-bottom:1rem;}
.preview-box{background:rgba(15,23,42,.8);border:2px dashed rgba(59,130,246,.5);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;}
.preview-box.has-image{border-style:solid;border-color:var(--success);padding:1rem;}
.upload-icon{font-size:3rem;color:var(--accent-blue);margin-bottom:1rem;opacity:.7;}
.char-counter{font-size:.85rem;color:var(--text-muted);text-align:right;margin-top:.25rem;}
</style>';
require_once __DIR__ . '/../includes/header_app.php';
?>
<div class="container py-4">
    <div class="create-header fade-in">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2 fw-bold"><i class="fas fa-bullhorn me-2"></i>Buat Aspirasi Baru</h2>
                <p class="mb-0 opacity-75">Sampaikan Aspirasi Anda tentang sarana dan prasarana sekolah</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="text-white-50"><i class="fas fa-calendar-alt me-2"></i><?= date('d F Y') ?></div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="form-card fade-in">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" id="aspirasiForm">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-user me-2"></i>Identitas Pelapor</div>
                            <label for="nis" class="form-label">NIS <span class="badge bg-danger">WAJIB</span></label>
                            <input type="text" class="form-control <?= isset($errors['nis']) ? 'is-invalid' : '' ?>"
                                   id="nis" name="nis" value="<?= e($_POST['nis'] ?? '') ?>"
                                   placeholder="Masukkan NIS Anda" maxlength="10" required>
                            <?php if (isset($errors['nis'])): ?>
                                <div class="invalid-feedback"><?= e($errors['nis']) ?></div>
                            <?php endif; ?>
                            <div id="nis-info" class="mt-3"></div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-clipboard-list me-2"></i>Detail Aspirasi</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="id_kategori" class="form-label">Kategori <span class="badge bg-danger">WAJIB</span></label>
                                    <select class="form-select <?= isset($errors['id_kategori']) ? 'is-invalid' : '' ?>" id="id_kategori" name="id_kategori" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach ($kategoris as $k): ?>
                                        <option value="<?= $k['id_kategori'] ?>" <?= ($_POST['id_kategori'] ?? '') == $k['id_kategori'] ? 'selected' : '' ?>>
                                            <?= e($k['ket_kategori']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['id_kategori'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['id_kategori']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="lokasi" class="form-label">Lokasi Kejadian <span class="badge bg-danger">WAJIB</span></label>
                                    <input type="text" class="form-control <?= isset($errors['lokasi']) ? 'is-invalid' : '' ?>"
                                           id="lokasi" name="lokasi" value="<?= e($_POST['lokasi'] ?? '') ?>"
                                           placeholder="Contoh: Ruang Kelas XII IPA 1" maxlength="50" required>
                                    <?php if (isset($errors['lokasi'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['lokasi']) ?></div>
                                    <?php endif; ?>
                                    <div class="char-counter" id="lokasi-counter">0 / 50</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="ket" class="form-label">Keterangan Detail <span class="badge bg-danger">WAJIB</span></label>
                                <textarea class="form-control <?= isset($errors['ket']) ? 'is-invalid' : '' ?>"
                                          id="ket" name="ket" rows="5"
                                          placeholder="Jelaskan secara detail kondisi atau masalah yang ingin dilaporkan..." required><?= e($_POST['ket'] ?? '') ?></textarea>
                                <?php if (isset($errors['ket'])): ?>
                                    <div class="invalid-feedback"><?= e($errors['ket']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-camera me-2"></i>Foto Pendukung (Opsional)</div>
                            <input type="file" class="d-none" id="foto" name="foto" accept="image/jpeg,image/png,image/jpg,image/gif">
                            <div class="preview-box" id="preview-box" onclick="document.getElementById('foto').click()">
                                <div id="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <h5 class="text-white mb-2">Klik untuk upload foto</h5>
                                    <p class="text-muted-custom mb-0">JPG, PNG, GIF - Maksimal 2MB</p>
                                </div>
                                <div id="preview-container" style="display:none;">
                                    <img id="preview-image" src="" alt="Preview" class="img-fluid rounded" style="max-height:300px;">
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="event.stopPropagation();removeImage()">
                                            <i class="fas fa-trash me-2"></i>Hapus Foto
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($errors['foto'])): ?>
                                <div class="text-danger mt-2"><i class="fas fa-exclamation-circle me-1"></i><?= e($errors['foto']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-3 mt-3">
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Aspirasi
                            </button>
                            <a href="<?= url() ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$extraScripts = '<script>
document.addEventListener("DOMContentLoaded", function() {
    const nisInput = document.getElementById("nis");
    const infoDiv  = document.getElementById("nis-info");
    nisInput.addEventListener("blur", function() {
        const nis = this.value.trim();
        if (!nis) { infoDiv.innerHTML = ""; return; }
        infoDiv.innerHTML = "<div class=\"alert alert-info mb-0\"><i class=\"fas fa-spinner fa-spin me-2\"></i>Memverifikasi NIS...</div>";
        fetch("' . url('api/check-nis.php') . '?nis=" + encodeURIComponent(nis))
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    infoDiv.innerHTML = "<div class=\"alert alert-success mb-0\"><i class=\"fas fa-check-circle me-2\"></i><strong>NIS Valid!</strong> Kelas " + data.student.kelas + " - " + data.student.jurusan + "</div>";
                } else {
                    infoDiv.innerHTML = "<div class=\"alert alert-warning mb-0\"><i class=\"fas fa-exclamation-triangle me-2\"></i>" + data.message + "</div>";
                }
            })
            .catch(() => {
                infoDiv.innerHTML = "<div class=\"alert alert-danger mb-0\"><i class=\"fas fa-times-circle me-2\"></i>Gagal memverifikasi NIS.</div>";
            });
    });

    const lokasiInput   = document.getElementById("lokasi");
    const lokasiCounter = document.getElementById("lokasi-counter");
    lokasiInput.addEventListener("input", function() { lokasiCounter.textContent = this.value.length + " / 50"; });

    const fotoInput         = document.getElementById("foto");
    const previewBox        = document.getElementById("preview-box");
    const uploadPlaceholder = document.getElementById("upload-placeholder");
    const previewContainer  = document.getElementById("preview-container");
    const previewImage      = document.getElementById("preview-image");

    fotoInput.addEventListener("change", function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2097152) { alert("Ukuran file terlalu besar! Maksimal 2MB"); this.value = ""; return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            uploadPlaceholder.style.display = "none";
            previewContainer.style.display  = "block";
            previewBox.classList.add("has-image");
        };
        reader.readAsDataURL(file);
    });

    window.removeImage = function() {
        fotoInput.value = "";
        uploadPlaceholder.style.display = "block";
        previewContainer.style.display  = "none";
        previewBox.classList.remove("has-image");
    };
});
</script>';
require_once __DIR__ . '/../includes/footer_app.php';
?>
