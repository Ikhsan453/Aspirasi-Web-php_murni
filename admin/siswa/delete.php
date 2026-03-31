<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
//VALIDASI METHOD POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('admin/siswa/index.php')); exit; }
//VALIDASI CSRF
verifyCsrf();

//INISIALISASI DATABASE
$db  = getDB();
$nis = trim($_POST['nis'] ?? '');

//DELETE RELATED DATA (CASCADE DELETE)
//HAPUS HISTORY STATUS
$db->prepare("DELETE sh FROM tb_aspirasi_status_history sh
    INNER JOIN tb_input_aspirasi ia ON sh.id_pelaporan = ia.id_pelaporan
    WHERE ia.nis = ?")->execute([$nis]);
//HAPUS ASPIRASI
$db->prepare("DELETE a FROM tb_aspirasi a
    INNER JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
    WHERE ia.nis = ?")->execute([$nis]);
//HAPUS INPUT ASPIRASI
$db->prepare("DELETE FROM tb_input_aspirasi WHERE nis = ?")->execute([$nis]);
//HAPUS DATA SISWA
$db->prepare("DELETE FROM tb_siswa WHERE nis = ?")->execute([$nis]);

//REDIRECT
setFlash('success', 'Siswa berhasil dihapus.');
header('Location: ' . url('admin/siswa/index.php'));
exit;
