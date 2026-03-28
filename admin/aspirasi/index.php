<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$db      = getDB();
$search  = trim($_GET['search'] ?? '');
$status  = trim($_GET['status'] ?? '');
$perPage = (int)($_GET['per_page'] ?? 10);
$page    = max(1, (int)($_GET['page'] ?? 1));
if (!in_array($perPage, [5,10,25,50,100])) $perPage = 10;

$conditions = []; $params = [];
if ($search) {
    $conditions[] = "(ia.nis LIKE ? OR ia.lokasi LIKE ? OR ia.ket LIKE ? OR k.ket_kategori LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($status) {
    $conditions[] = "COALESCE(a.status,'Menunggu') = ?";
    $params[] = $status;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$countSql = "SELECT COUNT(*) FROM tb_input_aspirasi ia
    LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan
    LEFT JOIN tb_kategori k ON ia.id_kategori=k.id_kategori $where";
$total = $db->prepare($countSql);
$total->execute($params);
$totalRows = (int)$total->fetchColumn();

$baseUrl   = url('admin/aspirasi/index.php') . "?search=" . urlencode($search) . "&status=" . urlencode($status) . "&per_page=$perPage";
$pagination = paginate($totalRows, $perPage, $page, $baseUrl);

$stmt = $db->prepare("SELECT ia.*, COALESCE(a.status,'Menunggu') as status, k.ket_kategori
    FROM tb_input_aspirasi ia
    LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan
    LEFT JOIN tb_kategori k ON ia.id_kategori=k.id_kategori
    $where ORDER BY ia.id_pelaporan DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $pagination['offset']]));
$aspirasis = $stmt->fetchAll();

// Hitung statistik
$stats = $db->query("SELECT
    COUNT(*) as total,
    SUM(CASE WHEN COALESCE(a.status,'Menunggu')='Menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN a.status='Proses' THEN 1 ELSE 0 END) as proses,
    SUM(CASE WHEN a.status='Selesai' THEN 1 ELSE 0 END) as selesai
    FROM tb_input_aspirasi ia
    LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan")->fetch();

$pageTitle = 'Manajemen Aspirasi';
require_once __DIR__ . '/../../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h4 class="text-white fw-bold mb-1"><i class="fas fa-comments me-2"></i>Manajemen Aspirasi</h4>
        <small class="text-muted-custom">Total <?= $stats['total'] ?> aspirasi masuk</small>
    </div>
</div>

<!-- Statistik bar -->
<div class="row g-3 mb-4">
    <?php foreach ([
        ['bg-primary','fa-comments','Total',$stats['total']],
        ['bg-warning','fa-clock','Menunggu',$stats['menunggu']],
        ['bg-info','fa-spinner','Proses',$stats['proses']],
        ['bg-success','fa-check-circle','Selesai',$stats['selesai']],
    ] as $st): ?>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3 slide-in" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);">
            <div class="card-body p-2">
                <i class="fas <?= $st[1] ?> fa-lg mb-2 <?= $st[0] === 'bg-primary' ? 'text-primary' : ($st[0] === 'bg-warning' ? 'text-warning' : ($st[0] === 'bg-info' ? 'text-info' : 'text-success')) ?>"></i>
                <div class="h4 fw-bold text-white mb-0"><?= (int)$st[3] ?></div>
                <small class="text-muted-custom"><?= $st[2] ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card slide-in">
    <div class="card-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari NIS, lokasi, kategori..." value="<?= e($search) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <?php foreach (['Menunggu','Proses','Selesai'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <?php foreach ([5,10,25,50,100] as $pp): ?>
                    <option value="<?= $pp ?>" <?= $pp===$perPage?'selected':'' ?>><?= $pp ?> / hal</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
                <?php if ($search || $status): ?>
                <a href="<?= url('admin/aspirasi/index.php') ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aspirasis)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block" style="color:rgba(100,116,139,.5)"></i>
                            <div class="text-muted-custom">Belum ada aspirasi</div>
                            <?php if ($search || $status): ?>
                            <a href="<?= url('admin/aspirasi/index.php') ?>" class="btn btn-sm btn-secondary mt-3">Reset Filter</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: foreach ($aspirasis as $a):
                        $s  = $a['status'];
                        $bc = match($s) { 'Menunggu'=>'bg-warning','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
                        $ic = match($s) { 'Menunggu'=>'fa-clock','Proses'=>'fa-spinner','Selesai'=>'fa-check-circle',default=>'fa-question' };
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= $a['id_pelaporan'] ?></span></td>
                        <td>
                            <div class="text-white"><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
                            <small class="text-muted-custom"><?= date('H:i', strtotime($a['created_at'])) ?></small>
                        </td>
                        <td class="fw-semibold text-white"><?= e($a['nis']) ?></td>
                        <td><span class="badge bg-warning"><?= e($a['ket_kategori'] ?? '-') ?></span></td>
                        <td class="text-white"><?= e(strLimit($a['lokasi'], 25)) ?></td>
                        <td><span class="badge <?= $bc ?>"><i class="fas <?= $ic ?> me-1"></i><?= $s ?></span></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= url('admin/aspirasi/show.php') ?>?id=<?= $a['id_pelaporan'] ?>"
                                   class="btn btn-sm btn-primary" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" action="<?= url('admin/aspirasi/delete.php') ?>"
                                      onsubmit="return confirm('Hapus aspirasi #<?= $a['id_pelaporan'] ?>?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $a['id_pelaporan'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted-custom">
            Menampilkan <?= min($pagination['offset']+1, $totalRows) ?>–<?= min($pagination['offset']+$perPage, $totalRows) ?> dari <?= $totalRows ?> data
        </small>
        <?= paginationLinks($pagination) ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
