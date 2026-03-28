<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT ia.*, COALESCE(a.status,'Menunggu') as status, a.feedback,
    k.ket_kategori, s.kelas, s.jurusan
    FROM tb_input_aspirasi ia
    LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan
    LEFT JOIN tb_kategori k ON ia.id_kategori=k.id_kategori
    LEFT JOIN tb_siswa s ON ia.nis=s.nis
    WHERE ia.id_pelaporan=?");
$stmt->execute([$id]);
$aspirasi = $stmt->fetch();
if (!$aspirasi) { header('Location: ' . url('admin/aspirasi/index.php')); exit; }

$hist = $db->prepare("SELECT * FROM tb_aspirasi_status_history WHERE id_pelaporan=? ORDER BY created_at DESC");
$hist->execute([$id]);
$histRows = $hist->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $newStatus = $_POST['status'] ?? '';
    $feedback  = trim($_POST['feedback'] ?? '');
    if (!in_array($newStatus, ['Menunggu','Proses','Selesai'])) {
        $errors[] = 'Status tidak valid.';
    }
    if (empty($errors)) {
        $adminUser = getAdminUser();
        $changedBy = $adminUser['username'] ?? 'admin';

        $db->prepare("INSERT INTO tb_aspirasi_status_history (id_pelaporan,status,feedback,changed_by) VALUES (?,?,?,?)")
           ->execute([$id, $newStatus, $feedback ?: null, $changedBy]);

        $katStmt = $db->prepare("SELECT ket_kategori FROM tb_kategori WHERE id_kategori = ?");
        $katStmt->execute([$aspirasi['id_kategori']]);
        $ketKategori = $katStmt->fetchColumn();

        $cekAsp = $db->prepare("SELECT id_aspirasi FROM tb_aspirasi WHERE id_pelaporan = ?");
        $cekAsp->execute([$id]);
        if ($cekAsp->fetch()) {
            $db->prepare("UPDATE tb_aspirasi SET status=?, feedback=?, ket_kategori=? WHERE id_pelaporan=?")
               ->execute([$newStatus, $feedback ?: null, $ketKategori, $id]);
        } else {
            $db->prepare("INSERT INTO tb_aspirasi (id_pelaporan,id_kategori,ket_kategori,status,feedback) VALUES (?,?,?,?,?)")
               ->execute([$id, $aspirasi['id_kategori'], $ketKategori, $newStatus, $feedback ?: null]);
        }

        setFlash('success', 'Status aspirasi berhasil diupdate.');
        header('Location: ' . url('admin/aspirasi/show.php') . '?id=' . $id);
        exit;
    }
}

$status     = $aspirasi['status'];
$badgeClass = match($status) { 'Menunggu'=>'bg-warning','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
$badgeIcon  = match($status) { 'Menunggu'=>'fa-clock','Proses'=>'fa-spinner','Selesai'=>'fa-check-circle',default=>'fa-question' };
$pageTitle  = 'Detail Aspirasi #' . $id;
require_once __DIR__ . '/../../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h4 class="text-white fw-bold mb-1">
            <i class="fas fa-eye me-2"></i>Detail Aspirasi
            <span class="badge bg-secondary ms-1">#<?= $id ?></span>
        </h4>
        <small class="text-muted-custom">
            Dilaporkan pada <?= date('d F Y H:i', strtotime($aspirasi['created_at'])) ?>
        </small>
    </div>
    <a href="<?= url('admin/aspirasi/index.php') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Kolom kiri: Info aspirasi -->
    <div class="col-lg-7">
        <div class="card h-100 slide-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="text-white mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2"></i>Informasi Aspirasi
                </h6>
                <span class="badge <?= $badgeClass ?>">
                    <i class="fas <?= $badgeIcon ?> me-1"></i><?= $status ?>
                </span>
            </div>
            <div class="card-body">
                <!-- Info grid -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                            <small class="text-muted-custom d-block mb-1"><i class="fas fa-hashtag me-1"></i>ID Pelaporan</small>
                            <span class="text-white fw-bold">#<?= $aspirasi['id_pelaporan'] ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                            <small class="text-muted-custom d-block mb-1"><i class="fas fa-id-card me-1"></i>NIS</small>
                            <span class="text-white fw-bold"><?= e($aspirasi['nis']) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                            <small class="text-muted-custom d-block mb-1"><i class="fas fa-graduation-cap me-1"></i>Kelas</small>
                            <span class="text-white"><?= e($aspirasi['kelas']??'-') ?> <?= e($aspirasi['jurusan']??'') ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                            <small class="text-muted-custom d-block mb-1"><i class="fas fa-tag me-1"></i>Kategori</small>
                            <span class="badge bg-warning"><?= e($aspirasi['ket_kategori']??'-') ?></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                            <small class="text-muted-custom d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Lokasi</small>
                            <span class="text-white"><?= e($aspirasi['lokasi']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="mb-4">
                    <h6 class="text-white fw-semibold mb-2"><i class="fas fa-comment-alt me-2"></i>Keterangan</h6>
                    <div class="p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                        <p class="text-white mb-0" style="white-space:pre-wrap;line-height:1.7"><?= e($aspirasi['ket']) ?></p>
                    </div>
                </div>

                <!-- Foto -->
                <?php if ($aspirasi['foto']): ?>
                <div>
                    <h6 class="text-white fw-semibold mb-2"><i class="fas fa-camera me-2"></i>Foto Pendukung</h6>
                    <div class="text-center p-3 rounded" style="background:rgba(51,65,85,0.3);border:1px solid rgba(100,116,139,0.2);">
                        <img src="<?= url('uploads/aspirasi/' . e($aspirasi['foto'])) ?>"
                             alt="Foto Aspirasi" class="img-fluid rounded shadow"
                             style="max-height:350px;cursor:pointer;"
                             onclick="window.open(this.src,'_blank')">
                        <div class="mt-2"><small class="text-muted-custom">Klik foto untuk memperbesar</small></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom kanan: Update status + riwayat -->
    <div class="col-lg-5">
        <!-- Form update status -->
        <div class="card mb-4 slide-in">
            <div class="card-header">
                <h6 class="text-white mb-0 fw-semibold"><i class="fas fa-edit me-2"></i>Update Status</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-lg" required>
                            <?php foreach (['Menunggu','Proses','Selesai'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Feedback <small class="text-muted-custom fw-normal">(opsional)</small></label>
                        <textarea name="feedback" class="form-control" rows="4"
                                  placeholder="Berikan keterangan atau tindakan yang sudah dilakukan..."><?= e($aspirasi['feedback'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Simpan Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Riwayat status -->
        <div class="card slide-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="text-white mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Status</h6>
                <span class="badge bg-secondary"><?= count($histRows) ?> entri</span>
            </div>
            <div class="card-body p-0" style="max-height:350px;overflow-y:auto;">
                <?php if (empty($histRows)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-history fa-2x mb-2 d-block" style="color:rgba(100,116,139,.4)"></i>
                    <small class="text-muted-custom">Belum ada riwayat perubahan</small>
                </div>
                <?php else: foreach ($histRows as $i => $h):
                    $hBadge = match($h['status']) { 'Menunggu'=>'bg-warning','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
                    $hIcon  = match($h['status']) { 'Menunggu'=>'fa-clock','Proses'=>'fa-spinner','Selesai'=>'fa-check-circle',default=>'fa-question' };
                ?>
                <div class="p-3 <?= $i < count($histRows)-1 ? '' : '' ?>"
                     style="border-bottom:1px solid rgba(51,65,85,0.5);<?= $i===0 ? 'background:rgba(59,130,246,0.05)' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge <?= $hBadge ?>">
                            <i class="fas <?= $hIcon ?> me-1"></i><?= $h['status'] ?>
                        </span>
                        <small class="text-muted-custom"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></small>
                    </div>
                    <?php if ($h['feedback']): ?>
                    <p class="text-white mb-1 small" style="line-height:1.5"><?= e($h['feedback']) ?></p>
                    <?php endif; ?>
                    <small class="text-muted-custom">
                        <i class="fas fa-user me-1"></i><?= e($h['changed_by']) ?>
                    </small>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
