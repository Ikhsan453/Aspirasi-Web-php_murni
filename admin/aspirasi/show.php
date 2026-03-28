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

// Handle update status
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

        // Catat ke history
        $db->prepare("INSERT INTO tb_aspirasi_status_history (id_pelaporan,status,feedback,changed_by) VALUES (?,?,?,?)")
           ->execute([$id, $newStatus, $feedback ?: null, $changedBy]);

        // Ambil ket_kategori
        $katStmt = $db->prepare("SELECT ket_kategori FROM tb_kategori WHERE id_kategori = ?");
        $katStmt->execute([$aspirasi['id_kategori']]);
        $ketKategori = $katStmt->fetchColumn();

        // Update atau insert tb_aspirasi
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

$status = $aspirasi['status'];
$badgeClass = match($status) { 'Menunggu'=>'bg-warning text-dark','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
$pageTitle = 'Detail Aspirasi #' . $id;
require_once __DIR__ . '/../../includes/header_admin.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-eye me-2"></i>Detail Aspirasi #<?= $id ?></h4>
    <a href="<?= url('admin/aspirasi/index.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><?php foreach ($errors as $e) echo '<div>' . e($e) . '</div>'; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;">
            <div class="card-header"><h6 class="text-white mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Informasi Aspirasi</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td style="width:35%;color:rgba(203,213,225,.8)">ID Pelaporan</td><td class="text-white"><strong>#<?= $aspirasi['id_pelaporan'] ?></strong></td></tr>
                    <tr><td style="color:rgba(203,213,225,.8)">Tanggal</td><td class="text-white"><?= date('d/m/Y H:i', strtotime($aspirasi['created_at'])) ?></td></tr>
                    <tr><td style="color:rgba(203,213,225,.8)">NIS</td><td class="text-white"><strong><?= e($aspirasi['nis']) ?></strong></td></tr>
                    <tr><td style="color:rgba(203,213,225,.8)">Kelas</td><td class="text-white"><?= e($aspirasi['kelas']??'-') ?> <?= e($aspirasi['jurusan']??'') ?></td></tr>
                    <tr><td style="color:rgba(203,213,225,.8)">Kategori</td><td><span class="badge bg-warning text-dark"><?= e($aspirasi['ket_kategori']??'-') ?></span></td></tr>
                    <tr><td style="color:rgba(203,213,225,.8)">Lokasi</td><td class="text-white"><?= e($aspirasi['lokasi']) ?></td></tr>
                    <tr><td style="color:rgba(203,213,225,.8)">Status</td><td><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td></tr>
                </table>
                <hr style="border-color:rgba(51,65,85,.5)">
                <h6 class="text-white fw-semibold mb-2">Keterangan</h6>
                <p class="text-light" style="white-space:pre-wrap;"><?= e($aspirasi['ket']) ?></p>
                <?php if ($aspirasi['foto']): ?>
                <h6 class="text-white fw-semibold mb-2 mt-3">Foto Pendukung</h6>
                <img src="<?= url('uploads/aspirasi/' . e($aspirasi['foto'])) ?>" alt="Foto" class="img-fluid rounded" style="max-height:300px;">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card mb-4" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;">
            <div class="card-header"><h6 class="text-white mb-0 fw-semibold"><i class="fas fa-edit me-2"></i>Update Status</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['Menunggu','Proses','Selesai'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback (opsional)</label>
                        <textarea name="feedback" class="form-control" rows="3"
                                  placeholder="Berikan feedback untuk aspirasi ini..."><?= e($aspirasi['feedback'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update Status</button>
                </form>
            </div>
        </div>

        <div class="card" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;">
            <div class="card-header"><h6 class="text-white mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Status</h6></div>
            <div class="card-body" style="max-height:300px;overflow-y:auto;">
                <?php if (empty($histRows)): ?>
                <p class="text-muted-custom text-center mb-0">Belum ada riwayat</p>
                <?php else: foreach ($histRows as $h):
                    $hBadge = match($h['status']) { 'Menunggu'=>'bg-warning text-dark','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
                ?>
                <div class="mb-3 pb-3" style="border-bottom:1px solid rgba(100,116,139,.3);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge <?= $hBadge ?>"><?= $h['status'] ?></span>
                            <?php if ($h['feedback']): ?>
                            <div class="mt-1"><small class="text-light"><?= e($h['feedback']) ?></small></div>
                            <?php endif; ?>
                            <div><small class="text-muted-custom"><i class="fas fa-user me-1"></i><?= e($h['changed_by']) ?></small></div>
                        </div>
                        <small class="text-muted-custom"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></small>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
