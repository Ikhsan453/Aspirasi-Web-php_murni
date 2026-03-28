<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$idPelaporan = $_SESSION['last_id_pelaporan'] ?? null;
unset($_SESSION['last_id_pelaporan']);

$pageTitle = 'Aspirasi Berhasil Dikirim';
require_once __DIR__ . '/../includes/header_app.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">
                    <div class="mb-4"><i class="fas fa-check-circle fa-5x text-success"></i></div>
                    <h3 class="mb-3 text-white">Aspirasi Berhasil Dikirim!</h3>
                    <?php if ($idPelaporan): ?>
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-hashtag me-2"></i>Nomor Laporan Anda: <strong>#<?= (int)$idPelaporan ?></strong>
                        <div class="mt-1"><small>Simpan nomor ini untuk memantau status aspirasi Anda.</small></div>
                    </div>
                    <?php endif; ?>
                    <p class="text-muted-custom mb-4">Terima kasih telah melaporkan Aspirasi Anda. Tim kami akan segera menindaklanjuti laporan Anda.</p>
                    <div class="d-grid gap-2">
                        <a href="<?= url('aspirasi/status.php') ?>" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Cek Status Aspirasi
                        </a>
                        <a href="<?= url('aspirasi/create.php') ?>" class="btn btn-info">
                            <i class="fas fa-plus-circle me-2"></i>Buat Aspirasi Lagi
                        </a>
                        <a href="<?= url() ?>" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_app.php'; ?>
