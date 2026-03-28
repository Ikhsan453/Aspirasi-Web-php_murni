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

$baseUrl = url('admin/aspirasi/index.php') . "?search=" . urlencode($search) . "&status=" . urlencode($status) . "&per_page=$perPage";
$pagination = paginate($totalRows, $perPage, $page, $baseUrl);

$stmt = $db->prepare("SELECT ia.*, COALESCE(a.status,'Menunggu') as status, k.ket_kategori
    FROM tb_input_aspirasi ia
    LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan
    LEFT JOIN tb_kategori k ON ia.id_kategori=k.id_kategori
    $where ORDER BY ia.id_pelaporan ASC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $pagination['offset']]));
$aspirasis = $stmt->fetchAll();

$pageTitle = 'Manajemen Aspirasi';
require_once __DIR__ . '/../../includes/header_admin.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-comments me-2"></i>Manajemen Aspirasi</h4>
</div>
<div class="card" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control" style="max-width:220px;"
                   placeholder="Cari NIS, lokasi, kategori..." value="<?= e($search) ?>">
            <select name="status" class="form-select" style="width:140px;">
                <option value="">Semua Status</option>
                <?php foreach (['Menunggu','Proses','Selesai'] as $s): ?>
                <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <select name="per_page" class="form-select" style="width:80px;" onchange="this.form.submit()">
                <?php foreach ([5,10,25,50,100] as $pp): ?>
                <option value="<?= $pp ?>" <?= $pp===$perPage?'selected':'' ?>><?= $pp ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <?php if ($search || $status): ?>
            <a href="<?= url('admin/aspirasi/index.php') ?>" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:var(--gradient-accent);">
                    <tr>
                        <th class="text-white">ID</th>
                        <th class="text-white">Tanggal</th>
                        <th class="text-white">NIS</th>
                        <th class="text-white">Kategori</th>
                        <th class="text-white">Lokasi</th>
                        <th class="text-white">Status</th>
                        <th class="text-white text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aspirasis)): ?>
                    <tr><td colspan="7" class="text-center text-muted-custom py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada aspirasi</td></tr>
                    <?php else: foreach ($aspirasis as $a):
                        $s = $a['status'];
                        $bc = match($s) { 'Menunggu'=>'bg-warning text-dark','Proses'=>'bg-info','Selesai'=>'bg-success',default=>'bg-secondary' };
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= $a['id_pelaporan'] ?></span></td>
                        <td class="text-light">
                            <div><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
                            <small class="text-muted-custom"><?= date('H:i', strtotime($a['created_at'])) ?></small>
                        </td>
                        <td class="text-light fw-semibold"><?= e($a['nis']) ?></td>
                        <td><span class="badge bg-warning text-dark"><?= e($a['ket_kategori']) ?></span></td>
                        <td class="text-light"><?= e(strLimit($a['lokasi'], 25)) ?></td>
                        <td><span class="badge <?= $bc ?>"><?= $s ?></span></td>
                        <td class="text-center">
                            <a href="<?= url('admin/aspirasi/show.php') ?>?id=<?= $a['id_pelaporan'] ?>" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="<?= url('admin/aspirasi/delete.php') ?>" class="d-inline"
                                  onsubmit="return confirm('Hapus aspirasi ini?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $a['id_pelaporan'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer"><?= paginationLinks($pagination) ?></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer_admin.php'; ?>
