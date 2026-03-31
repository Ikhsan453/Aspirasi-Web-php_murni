<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

//INISIALISASI DATABASE & INPUT
$db      = getDB();
$nis     = trim($_POST['nis'] ?? $_GET['nis'] ?? '');
$perPage = (int)($_POST['per_page'] ?? $_GET['per_page'] ?? 10);
$page    = max(1, (int)($_GET['page'] ?? 1));
//VALIDASI PER_PAGE
if (!in_array($perPage, [5,10,25,50,100])) $perPage = 10;

//INISIALISASI ARRAY
$aspirasis     = [];
$totalAspirasi = 0;
$menunggu = $proses = $selesai = 0;
$pagination    = null;
$searched      = false;

//VALIDASI CSRF JIK POST
if ($nis && $_SERVER['REQUEST_METHOD'] === 'POST') verifyCsrf();

//PROSES PENCARIAN
if ($nis) {
    $searched = true;
    //CEK DAN HITUNG STATUS ASPIRASI
    $allRows  = $db->prepare("SELECT COALESCE(a.status,'Menunggu') as status FROM tb_input_aspirasi ia LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan WHERE ia.nis=?");
    $allRows->execute([$nis]);
    foreach ($allRows->fetchAll() as $row) {
        $totalAspirasi++;
        if ($row['status']==='Menunggu') $menunggu++;
        elseif ($row['status']==='Proses') $proses++;
        elseif ($row['status']==='Selesai') $selesai++;
    }

    //HITUNG PAGINATION
    $pagination = paginate($totalAspirasi, $perPage, $page, url('aspirasi/status.php') . "?nis=" . urlencode($nis) . "&per_page=$perPage");

    //QUERY DATA ASPIRASI DENGAN PAGINATION
    $stmt = $db->prepare("
        SELECT ia.*, COALESCE(a.status,'Menunggu') as status, a.feedback, a.updated_at as aspirasi_updated,
               k.ket_kategori, s.kelas, s.jurusan
        FROM tb_input_aspirasi ia
        LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan
        LEFT JOIN tb_kategori k ON ia.id_kategori=k.id_kategori
        LEFT JOIN tb_siswa s ON ia.nis=s.nis
        WHERE ia.nis=?
        ORDER BY ia.id_pelaporan ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$nis, $perPage, $pagination['offset']]);
    $aspirasis = $stmt->fetchAll();
}

$pageTitle   = 'Cek Status Aspirasi';
$extraStyles = '<style>
.data-table-card{background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;}
.stats-bar{background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.3);border-radius:12px;padding:1rem;margin-bottom:1.5rem;}
</style>';
require_once __DIR__ . '/../includes/header_app.php';
?>
<div class="container">
    <div class="card border-0 shadow-lg mb-4 fade-in" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5)!important;border-radius:16px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <h4 class="mb-1 text-white fw-semibold"><i class="fas fa-search me-2"></i>Cek Status Aspirasi</h4>
                    <p class="mb-0 text-muted-custom small">Masukkan NIS untuk melihat aspirasi</p>
                </div>
                <div class="col-lg-8">
                    <form method="POST" id="searchForm">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-8">
                                <label for="nis" class="form-label fw-semibold text-light small mb-1">Nomor Induk Siswa (NIS)</label>
                                <input type="text" class="form-control form-control-lg" id="nis" name="nis"
                                       value="<?= e($nis) ?>" required maxlength="10" placeholder="Contoh: 1234567890" autofocus>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($searched): ?>
    <div class="stats-bar slide-in">
        <div class="row text-center">
            <div class="col-md-3"><div class="fw-bold text-light fs-4"><?= $totalAspirasi ?></div><small class="text-muted-custom">Total Aspirasi</small></div>
            <div class="col-md-3"><div class="fw-bold text-warning fs-4"><?= $menunggu ?></div><small class="text-muted-custom">Menunggu</small></div>
            <div class="col-md-3"><div class="fw-bold text-info fs-4"><?= $proses ?></div><small class="text-muted-custom">Dalam Proses</small></div>
            <div class="col-md-3"><div class="fw-bold text-success fs-4"><?= $selesai ?></div><small class="text-muted-custom">Selesai</small></div>
        </div>
    </div>

    <?php if (count($aspirasis) > 0): ?>
    <div class="card data-table-card slide-in">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="mb-0 text-white fw-semibold"><i class="fas fa-list me-2"></i>Daftar Aspirasi - NIS: <?= e($nis) ?></h5>
            <form method="POST" id="perPageForm" class="d-flex align-items-center gap-2">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="nis" value="<?= e($nis) ?>">
                <label class="text-white mb-0 small">Show:</label>
                <select class="form-select form-select-sm" name="per_page" style="width:80px;" onchange="this.form.submit()">
                    <?php foreach ([5,10,25,50,100] as $pp): ?>
                    <option value="<?= $pp ?>" <?= $pp===$perPage ? 'selected' : '' ?>><?= $pp ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:var(--gradient-accent);">
                        <tr>
                            <th class="text-white fw-semibold">ID</th>
                            <th class="text-white fw-semibold">Tanggal</th>
                            <th class="text-white fw-semibold">Kategori</th>
                            <th class="text-white fw-semibold">Lokasi</th>
                            <th class="text-white fw-semibold">Status</th>
                            <th class="text-white fw-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aspirasis as $a):
                            $status = $a['status'];
                            $badgeClass = match($status) { 'Menunggu'=>'bg-warning text-dark','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
                            $icon = match($status) { 'Menunggu'=>'fa-clock','Proses'=>'fa-spinner','Selesai'=>'fa-check-circle',default=>'fa-question' };
                        ?>
                        <tr>
                            <td><span class="badge bg-secondary fs-6">#<?= $a['id_pelaporan'] ?></span></td>
                            <td class="text-light">
                                <div><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
                                <small class="text-muted-custom"><?= date('H:i', strtotime($a['created_at'])) ?></small>
                            </td>
                            <td><span class="badge bg-warning text-dark fw-semibold"><?= e($a['ket_kategori'] ?? '-') ?></span></td>
                            <td class="text-light"><?= e(strLimit($a['lokasi'], 30)) ?></td>
                            <td><span class="badge <?= $badgeClass ?> fs-6"><i class="fas <?= $icon ?> me-1"></i><?= $status ?></span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal" data-bs-target="#modal<?= $a['id_pelaporan'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($pagination && $pagination['total_pages'] > 1): ?>
        <div class="card-footer"><?= paginationLinks($pagination) ?></div>
        <?php endif; ?>
    </div>

    <?php foreach ($aspirasis as $a):
        $status = $a['status'];
        $badgeClass = match($status) { 'Menunggu'=>'bg-warning text-dark','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
        $icon = match($status) { 'Menunggu'=>'fa-clock','Proses'=>'fa-spinner','Selesai'=>'fa-check-circle',default=>'fa-question' };
        $hist = $db->prepare("SELECT * FROM tb_aspirasi_status_history WHERE id_pelaporan=? ORDER BY created_at DESC");
        $hist->execute([$a['id_pelaporan']]);
        $histRows = $hist->fetchAll();
    ?>
    <div class="modal fade" id="modal<?= $a['id_pelaporan'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="background:rgba(30,41,59,.98);border:1px solid rgba(51,65,85,.5);">
                <div class="modal-header" style="border-bottom:1px solid rgba(51,65,85,.5);">
                    <h5 class="modal-title text-white fw-semibold"><i class="fas fa-eye me-2"></i>Detail Aspirasi #<?= $a['id_pelaporan'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div style="background:rgba(51,65,85,.3);border-radius:12px;padding:1.5rem;" class="mb-4">
                        <table class="table table-borderless mb-0">
                            <tr><td style="width:40%;color:rgba(203,213,225,.9)"><i class="fas fa-hashtag me-2"></i>ID</td><td class="text-white"><strong>#<?= $a['id_pelaporan'] ?></strong></td></tr>
                            <tr><td style="color:rgba(203,213,225,.9)"><i class="fas fa-calendar me-2"></i>Tanggal</td><td class="text-white"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td></tr>
                            <tr><td style="color:rgba(203,213,225,.9)"><i class="fas fa-id-card me-2"></i>NIS</td><td class="text-white"><strong><?= e($a['nis']) ?></strong></td></tr>
                            <tr><td style="color:rgba(203,213,225,.9)"><i class="fas fa-graduation-cap me-2"></i>Kelas</td><td class="text-white"><?= e($a['kelas']??'-') ?> <?= e($a['jurusan']??'') ?></td></tr>
                            <tr><td style="color:rgba(203,213,225,.9)"><i class="fas fa-tag me-2"></i>Kategori</td><td><span class="badge bg-warning text-dark px-3 py-2"><?= e($a['ket_kategori']??'-') ?></span></td></tr>
                            <tr><td style="color:rgba(203,213,225,.9)"><i class="fas fa-info-circle me-2"></i>Status</td><td><span class="badge <?= $badgeClass ?> px-3 py-2"><i class="fas <?= $icon ?> me-1"></i><?= $status ?></span></td></tr>
                            <tr><td style="color:rgba(203,213,225,.9)"><i class="fas fa-map-marker-alt me-2"></i>Lokasi</td><td class="text-white"><?= e($a['lokasi']) ?></td></tr>
                        </table>
                    </div>
                    <div class="mb-4">
                        <h6 class="text-white fw-semibold mb-3"><i class="fas fa-comment-alt me-2"></i>Keterangan</h6>
                        <div style="background:rgba(51,65,85,.3);border-radius:12px;padding:1.5rem;">
                            <p class="text-light mb-0" style="white-space:pre-wrap;"><?= e($a['ket']) ?></p>
                        </div>
                    </div>
                    <?php if ($a['foto']): ?>
                    <div class="mb-4">
                        <h6 class="text-white fw-semibold mb-3"><i class="fas fa-camera me-2"></i>Foto Pendukung</h6>
                        <div style="background:rgba(51,65,85,.3);border-radius:12px;padding:1rem;text-align:center;">
                            <img src="<?= url('uploads/aspirasi/' . e($a['foto'])) ?>" alt="Foto" class="img-fluid rounded" style="max-height:400px;">
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($a['feedback']): ?>
                    <div class="mb-4">
                        <h6 class="text-white fw-semibold mb-3"><i class="fas fa-comment-dots me-2"></i>Feedback dari Admin</h6>
                        <div style="background:rgba(6,182,212,.1);border:1px solid rgba(6,182,212,.3);border-radius:12px;padding:1.5rem;">
                            <p class="text-light mb-2"><?= e($a['feedback']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($histRows)): ?>
                    <div>
                        <h6 class="text-white fw-semibold mb-3"><i class="fas fa-history me-2"></i>Riwayat Status</h6>
                        <div style="background:rgba(51,65,85,.3);border-radius:12px;padding:1rem;">
                            <?php foreach ($histRows as $h):
                                $hBadge = match($h['status']) { 'Menunggu'=>'bg-warning text-dark','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
                            ?>
                            <div class="mb-3 pb-3" style="border-bottom:1px solid rgba(100,116,139,.3);">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge <?= $hBadge ?> px-3 py-2"><?= $h['status'] ?></span>
                                        <?php if ($h['feedback']): ?>
                                        <div class="mt-2"><small class="text-light"><?= e($h['feedback']) ?></small></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end ms-3" style="min-width:100px;">
                                        <small class="text-muted-custom d-block"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></small>
                                        <?php if ($h['changed_by']): ?>
                                        <small class="text-muted-custom d-block"><i class="fas fa-user me-1"></i><?= e($h['changed_by']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer" style="border-top:1px solid rgba(51,65,85,.5);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
            <h5 class="text-light mb-2">Tidak Ada Aspirasi</h5>
            <p class="text-muted-custom mb-4">Tidak ada Aspirasi ditemukan untuk NIS: <?= e($nis) ?></p>
            <a href="<?= url('aspirasi/create.php') ?>" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>Buat Aspirasi Baru</a>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="card border-0 shadow-sm slide-in">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-4x text-primary mb-4"></i>
            <h5 class="text-light mb-2">Cari Aspirasi Anda</h5>
            <p class="text-muted-custom">Masukkan NIS Anda di form di atas untuk melihat semua Aspirasi yang telah dilaporkan</p>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_app.php'; ?>
