<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$db     = getDB();
$search = trim($_GET['search'] ?? '');
$perPage = (int)($_GET['per_page'] ?? 10);
$page    = max(1, (int)($_GET['page'] ?? 1));
if (!in_array($perPage, [5,10,25,50,100])) $perPage = 10;

$where = '';
$params = [];
if ($search) {
    $where = "WHERE ket_kategori LIKE ?";
    $params[] = "%$search%";
}

$total = $db->prepare("SELECT COUNT(*) FROM tb_kategori $where");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();

$pagination = paginate($totalRows, $perPage, $page, url('admin/kategori/index.php') . "?search=" . urlencode($search) . "&per_page=$perPage");

$stmt = $db->prepare("SELECT * FROM tb_kategori $where ORDER BY id_kategori ASC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $pagination['offset']]));
$kategoris = $stmt->fetchAll();

$pageTitle = 'Manajemen Kategori';
require_once __DIR__ . '/../../includes/header_admin.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white fw-bold mb-0"><i class="fas fa-tags me-2"></i>Manajemen Kategori</h4>
    <a href="<?= url('admin/kategori/create.php') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Kategori
    </a>
</div>

<div class="card" style="background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control" style="max-width:250px;"
                   placeholder="Cari kategori..." value="<?= e($search) ?>">
            <select name="per_page" class="form-select" style="width:80px;" onchange="this.form.submit()">
                <?php foreach ([5,10,25,50,100] as $pp): ?>
                <option value="<?= $pp ?>" <?= $pp===$perPage?'selected':'' ?>><?= $pp ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <?php if ($search): ?>
            <a href="<?= url('admin/kategori/index.php') ?>" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:var(--gradient-accent);">
                    <tr>
                        <th class="text-white">No</th>
                        <th class="text-white">Nama Kategori</th>
                        <th class="text-white">Dibuat</th>
                        <th class="text-white text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kategoris)): ?>
                    <tr><td colspan="4" class="text-center text-muted-custom py-4">Belum ada kategori</td></tr>
                    <?php else: foreach ($kategoris as $i => $k): ?>
                    <tr>
                        <td class="text-light"><?= $pagination['offset'] + $i + 1 ?></td>
                        <td class="text-light fw-semibold"><?= e($k['ket_kategori']) ?></td>
                        <td class="text-light"><?= date('d/m/Y', strtotime($k['created_at'])) ?></td>
                        <td class="text-center">
                            <a href="<?= url('admin/kategori/edit.php') ?>?id=<?= $k['id_kategori'] ?>" class="btn btn-sm btn-warning me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url('admin/kategori/delete.php') ?>" class="d-inline"
                                  onsubmit="return confirm('Hapus kategori ini? Semua aspirasi terkait juga akan dihapus.')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $k['id_kategori'] ?>">
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
