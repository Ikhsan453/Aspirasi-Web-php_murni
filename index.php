<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Beranda - Aspirasi Web';

$db = getDB();
$totalAspirasi = $db->query("SELECT COUNT(*) FROM tb_input_aspirasi")->fetchColumn();
$totalKategori = $db->query("SELECT COUNT(*) FROM tb_kategori")->fetchColumn();
$totalSiswa    = $db->query("SELECT COUNT(*) FROM tb_siswa")->fetchColumn();

$extraStyles = '<style>
.hero-section{background:var(--gradient-accent);border-radius:24px;padding:4rem 3rem;margin-bottom:4rem;position:relative;overflow:hidden;}
.hero-section::before{content:"";position:absolute;top:-50%;right:-20%;width:300px;height:300px;background:rgba(255,255,255,.1);border-radius:50%;animation:float 6s ease-in-out infinite;}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
.feature-card{background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:20px;height:100%;transition:all .4s ease;position:relative;overflow:hidden;}
.feature-card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:var(--gradient-accent);transform:scaleX(0);transition:transform .4s ease;}
.feature-card:hover::before{transform:scaleX(1);}
.feature-card:hover{transform:translateY(-10px);box-shadow:var(--shadow-xl);border-color:var(--accent-blue);}
.feature-icon{width:80px;height:80px;background:var(--gradient-accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;}
.step-circle{width:80px;height:80px;background:var(--gradient-accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;position:relative;}
.step-circle::after{content:"";position:absolute;width:100%;height:100%;border:2px solid var(--accent-blue);border-radius:50%;animation:pulse 2s infinite;}
@keyframes pulse{0%{transform:scale(1);opacity:1}100%{transform:scale(1.2);opacity:0}}
.stats-section{background:rgba(30,41,59,.8);border-radius:20px;padding:3rem 2rem;margin:3rem 0;}
.stat-number{font-size:2.5rem;font-weight:800;color:var(--accent-blue);display:block;}
.cta-section{background:var(--gradient-primary);border-radius:20px;padding:3rem 2rem;text-align:center;border:1px solid var(--secondary-blue);}
.section-title{position:relative;display:inline-block;margin-bottom:3rem;}
.section-title::after{content:"";position:absolute;bottom:-10px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:var(--gradient-accent);border-radius:2px;}
</style>';

require_once __DIR__ . '/includes/header_app.php';
?>
<div class="container">
    <div class="hero-section fade-in">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold text-white mb-4"><i class="fas fa-bullhorn me-3"></i>Sistem Aspirasi Web</h1>
                <p class="lead text-white mb-4 opacity-90">Platform digital yang memudahkan siswa untuk melaporkan kerusakan atau masalah sarana dan prasarana sekolah.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-success btn-lg px-4 py-3 shadow-lg" href="<?= url('aspirasi/create.php') ?>" style="font-size:1.2rem;font-weight:800;">
                        <i class="fas fa-plus-circle me-2"></i>Buat Aspirasi Baru
                    </a>
                    <a class="btn btn-info btn-lg px-4 py-3 shadow-lg" href="<?= url('aspirasi/status.php') ?>" style="font-size:1.2rem;font-weight:800;">
                        <i class="fas fa-search me-2"></i>Cek Status Aspirasi
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-school text-white opacity-25" style="font-size:12rem;"></i>
            </div>
        </div>
    </div>

    <div class="stats-section slide-in">
        <div class="row">
            <div class="col-md-4 text-center">
                <span class="stat-number"><?= (int)$totalAspirasi ?></span>
                <h5 class="text-light mt-2">Total Aspirasi</h5>
                <p class="text-muted-custom mb-0">Laporan yang telah masuk</p>
            </div>
            <div class="col-md-4 text-center">
                <span class="stat-number"><?= (int)$totalKategori ?></span>
                <h5 class="text-light mt-2">Kategori Tersedia</h5>
                <p class="text-muted-custom mb-0">Jenis kerusakan yang dapat dilaporkan</p>
            </div>
            <div class="col-md-4 text-center">
                <span class="stat-number"><?= (int)$totalSiswa ?></span>
                <h5 class="text-light mt-2">Siswa Terdaftar</h5>
                <p class="text-muted-custom mb-0">Pengguna aktif sistem</p>
            </div>
        </div>
    </div>

    <div class="text-center mb-5">
        <h2 class="section-title text-light"><i class="fas fa-star me-2"></i>Fitur Unggulan</h2>
    </div>
    <div class="row mb-5">
        <?php foreach ([
            ['fas fa-camera','Upload Foto Bukti','Sertakan foto sebagai bukti untuk memperjelas kondisi kerusakan yang dilaporkan.'],
            ['fas fa-history','Tracking Real-time','Pantau perkembangan Aspirasi secara real-time dengan riwayat lengkap status penanganan.'],
            ['fas fa-tags','Kategori Lengkap','Pilih kategori yang sesuai untuk memudahkan proses penanganan oleh pihak sekolah.'],
        ] as $i => $f): ?>
        <div class="col-lg-4 mb-4">
            <div class="card feature-card slide-in" style="animation-delay:<?= $i*0.2 ?>s">
                <div class="card-body text-center p-4">
                    <div class="feature-icon"><i class="<?= $f[0] ?> fa-2x text-white"></i></div>
                    <h5 class="card-title text-light fw-bold mb-3"><?= $f[1] ?></h5>
                    <p class="card-text text-muted-custom"><?= $f[2] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mb-5">
        <h2 class="section-title text-light"><i class="fas fa-question-circle me-2"></i>Cara Menggunakan</h2>
    </div>
    <div class="row mb-5">
        <?php foreach ([
            ['Isi Form Aspirasi','Lengkapi data diri dan detail Aspirasi dengan informasi yang jelas'],
            ['Upload Foto','Sertakan foto sebagai bukti pendukung kondisi yang dilaporkan'],
            ['Submit Laporan','Kirim Aspirasi dan dapatkan nomor tracking untuk memantau status'],
            ['Pantau Progress','Cek status penanganan Aspirasi secara berkala hingga selesai'],
        ] as $i => $s): ?>
        <div class="col-md-3 text-center mb-4">
            <div class="slide-in" style="animation-delay:<?= $i*0.2 ?>s">
                <div class="step-circle"><span class="h3 mb-0 text-white fw-bold"><?= $i+1 ?></span></div>
                <h5 class="text-light fw-bold"><?= $s[0] ?></h5>
                <p class="text-muted-custom"><?= $s[1] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cta-section slide-in">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h3 class="text-light fw-bold mb-3"><i class="fas fa-rocket me-2"></i>Siap Membuat Aspirasi?</h3>
                <p class="text-muted-custom mb-4">Laporkan sekarang dan bantu menciptakan lingkungan belajar yang lebih baik.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= url('aspirasi/create.php') ?>" class="btn btn-success btn-lg px-4 shadow-lg" style="font-size:1.1rem;font-weight:800;">
                        <i class="fas fa-plus-circle me-2"></i>Mulai Buat Aspirasi
                    </a>
                    <a href="<?= url('aspirasi/status.php') ?>" class="btn btn-info btn-lg px-4 shadow-lg" style="font-size:1.1rem;font-weight:800;">
                        <i class="fas fa-search me-2"></i>Cek Status
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer_app.php'; ?>
