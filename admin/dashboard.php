<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Dashboard Admin';

$db          = getDB();
$allAspirasi = $db->query("SELECT COALESCE(a.status,'Menunggu') as status FROM tb_input_aspirasi ia LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan")->fetchAll();
$totalAspirasi = count($allAspirasi);
$aspirasiMenunggu = $aspirasiProses = $aspirasiSelesai = 0;
foreach ($allAspirasi as $row) {
    if ($row['status']==='Menunggu') $aspirasiMenunggu++;
    elseif ($row['status']==='Proses') $aspirasiProses++;
    elseif ($row['status']==='Selesai') $aspirasiSelesai++;
}

$recentAspirasi = $db->query("
    SELECT ia.*, COALESCE(a.status,'Menunggu') as status, k.ket_kategori
    FROM tb_input_aspirasi ia
    LEFT JOIN tb_aspirasi a ON ia.id_pelaporan=a.id_pelaporan
    LEFT JOIN tb_kategori k ON ia.id_kategori=k.id_kategori
    ORDER BY ia.created_at DESC LIMIT 5
")->fetchAll();

$kategoris = $db->query("
    SELECT k.*, COUNT(ia.id_pelaporan) as total
    FROM tb_kategori k
    LEFT JOIN tb_input_aspirasi ia ON k.id_kategori=ia.id_kategori
    GROUP BY k.id_kategori ORDER BY total DESC
")->fetchAll();

$extraStyles = '<style>
.stats-card{border:none;border-radius:16px;color:white;transition:all .3s ease;overflow:hidden;position:relative;}
.stats-card::before{content:"";position:absolute;top:0;right:0;width:100px;height:100px;background:rgba(255,255,255,.1);border-radius:50%;transform:translate(30px,-30px);}
.stats-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-xl);}
.stats-card.primary{background:var(--gradient-accent);}
.stats-card.warning{background:linear-gradient(135deg,var(--warning) 0%,#d97706 100%);}
.stats-card.info{background:linear-gradient(135deg,var(--info) 0%,#0891b2 100%);}
.stats-card.success{background:linear-gradient(135deg,var(--success) 0%,#059669 100%);}
.stats-icon{font-size:2.5rem;opacity:.8;}
.recent-card{background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:16px;}
.page-header{background:var(--gradient-accent);border-radius:16px;padding:2rem;margin-bottom:2rem;color:white;}
.category-progress{background:rgba(51,65,85,.3);border-radius:10px;height:8px;overflow:hidden;}
.category-progress .progress-bar{background:var(--gradient-accent);border-radius:10px;transition:width .6s ease;}
</style>';
require_once __DIR__ . '/../includes/header_admin.php';
?>
<div class="page-header fade-in">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2"><i class="fas fa-tachometer-alt me-3"></i>Dashboard Admin</h1>
            <p class="lead mb-0 opacity-75">Selamat datang, <?= e($admin['username']) ?>!</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="text-white-50"><i class="fas fa-calendar-alt me-2"></i><?= date('d F Y') ?></div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <?php foreach ([
        ['primary','fa-comments','Total Aspirasi',$totalAspirasi],
        ['warning','fa-clock','Menunggu Review',$aspirasiMenunggu],
        ['info','fa-cogs','Dalam Proses',$aspirasiProses],
        ['success','fa-check-circle','Selesai Ditangani',$aspirasiSelesai],
    ] as $i => $c): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card <?= $c[0] ?> slide-in" style="animation-delay:<?= $i*0.1 ?>s">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-uppercase fw-bold mb-1 opacity-75" style="font-size:.875rem;"><?= $c[2] ?></div>
                        <div class="h2 mb-0 fw-bold"><?= $c[3] ?></div>
                    </div>
                    <div class="col-auto"><i class="fas <?= $c[1] ?> stats-icon"></i></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card recent-card slide-in">
            <div class="card-header"><h5 class="mb-0 text-white fw-semibold"><i class="fas fa-list-alt me-2"></i>Aspirasi Terbaru</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:var(--gradient-accent);">
                            <tr>
                                <th class="text-white fw-semibold">ID</th>
                                <th class="text-white fw-semibold">Tanggal</th>
                                <th class="text-white fw-semibold">NIS</th>
                                <th class="text-white fw-semibold">Kategori</th>
                                <th class="text-white fw-semibold">Lokasi</th>
                                <th class="text-white fw-semibold">Status</th>
                                <th class="text-white fw-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentAspirasi)): ?>
                            <tr><td colspan="7" class="text-center text-muted-custom py-4"><i class="fas fa-inbox fa-2x mb-3 d-block"></i>Belum ada Aspirasi masuk</td></tr>
                            <?php else: foreach ($recentAspirasi as $a):
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
                                <td><span class="badge bg-warning text-dark fw-semibold"><?= e($a['ket_kategori']) ?></span></td>
                                <td class="text-light"><?= e(strLimit($a['lokasi'], 20)) ?></td>
                                <td><span class="badge <?= $bc ?> fw-semibold"><?= $s ?></span></td>
                                <td><a href="<?= url('admin/aspirasi/show.php') ?>?id=<?= $a['id_pelaporan'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>Detail</a></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalAspirasi > 0): ?>
                <div class="card-footer text-center" style="background:rgba(51,65,85,.3);">
                    <a href="<?= url('admin/aspirasi/index.php') ?>" class="btn btn-primary"><i class="fas fa-list me-2"></i>Lihat Semua Aspirasi</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card recent-card mb-4 slide-in">
            <div class="card-header"><h5 class="mb-0 text-white fw-semibold"><i class="fas fa-chart-pie me-2"></i>Statistik Kategori</h5></div>
            <div class="card-body">
                <?php if (empty($kategoris)): ?>
                <div class="text-center text-muted-custom py-3"><i class="fas fa-tags fa-2x mb-2 d-block"></i>Belum ada kategori</div>
                <?php else: foreach ($kategoris as $k):
                    $pct = $totalAspirasi > 0 ? round(($k['total']/$totalAspirasi)*100, 1) : 0;
                ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-light fw-medium"><?= e($k['ket_kategori']) ?></span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info"><?= $k['total'] ?></span>
                            <small class="text-muted-custom"><?= $pct ?>%</small>
                        </div>
                    </div>
                    <div class="category-progress"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
