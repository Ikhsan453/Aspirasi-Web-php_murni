<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

//INISIALISASI DATABASE & INPUT SEARCH
$db      = getDB();
$search  = trim($_GET['search'] ?? '');
$perPage = (int)($_GET['per_page'] ?? 10);
$page    = max(1, (int)($_GET['page'] ?? 1));
//VALIDASI PER_PAGE
if (!in_array($perPage, [5,10,25,50,100])) $perPage = 10;

//KONDISI SEARCH
$where = ''; $params = [];
if ($search) {
    $where = "WHERE nis LIKE ? OR kelas LIKE ? OR jurusan LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

//HITUNG TOTAL BARIS
$total = $db->prepare("SELECT COUNT(*) FROM tb_siswa $where");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();

//HITUNG PAGINATION
$pagination = paginate($totalRows, $perPage, $page, url('admin/siswa/index.php') . "?search=" . urlencode($search) . "&per_page=$perPage");

//QUERY DATA TABEL SISWA
$stmt = $db->prepare("SELECT * FROM tb_siswa $where ORDER BY nis ASC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $pagination['offset']]));
$siswas = $stmt->fetchAll();

$pageTitle = 'Manajemen Siswa';
require_once __DIR__ . '/../../includes/header_admin.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-users me-2"></i>Manajemen Siswa</h4>
    <a href="<?= url('admin/siswa/create.php') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah Siswa</a>
</div>
<div class="card" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control" style="max-width:250px;"
                   placeholder="Cari NIS, kelas, jurusan..." value="<?= e($search) ?>">
            <select name="per_page" class="form-select" style="width:80px;" onchange="this.form.submit()">
                <?php foreach ([5,10,25,50,100] as $pp): ?>
                <option value="<?= $pp ?>" <?= $pp===$perPage?'selected':'' ?>><?= $pp ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <?php if ($search): ?>
            <a href="<?= url('admin/siswa/index.php') ?>" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:var(--gradient-accent);">
                    <tr>
                        <th class="text-white">No</th>
                        <th class="text-white">NIS</th>
                        <th class="text-white">Kelas</th>
                        <th class="text-white">Jurusan</th>
                        <th class="text-white text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswas)): ?>
                    <tr><td colspan="5" class="text-center text-muted-custom py-4">Belum ada data siswa</td></tr>
                    <?php else: foreach ($siswas as $i => $s): ?>
                    <tr>
                        <td class="text-light"><?= $pagination['offset'] + $i + 1 ?></td>
                        <td class="text-light fw-semibold"><?= e($s['nis']) ?></td>
                        <td class="text-light"><?= e($s['kelas']) ?></td>
                        <td class="text-light"><?= e($s['jurusan']) ?></td>
                        <td class="text-center">
                            <a href="<?= url('admin/siswa/edit.php') ?>?nis=<?= urlencode($s['nis']) ?>" class="btn btn-sm btn-warning me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url('admin/siswa/delete.php') ?>" class="d-inline"
                                  onsubmit="return confirm('Hapus siswa ini? Semua aspirasi terkait juga akan dihapus.')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="nis" value="<?= e($s['nis']) ?>">
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
