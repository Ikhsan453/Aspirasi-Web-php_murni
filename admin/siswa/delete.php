<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('admin/siswa/index.php')); exit; }
verifyCsrf();

$db  = getDB();
$nis = trim($_POST['nis'] ?? '');

// Hapus riwayat status, aspirasi, input_aspirasi (cascade harusnya handle, tapi eksplisit lebih aman)
$db->prepare("DELETE sh FROM tb_aspirasi_status_history sh
    INNER JOIN tb_input_aspirasi ia ON sh.id_pelaporan = ia.id_pelaporan
    WHERE ia.nis = ?")->execute([$nis]);
$db->prepare("DELETE a FROM tb_aspirasi a
    INNER JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
    WHERE ia.nis = ?")->execute([$nis]);
$db->prepare("DELETE FROM tb_input_aspirasi WHERE nis = ?")->execute([$nis]);
$db->prepare("DELETE FROM tb_siswa WHERE nis = ?")->execute([$nis]);

setFlash('success', 'Siswa berhasil dihapus.');
header('Location: ' . url('admin/siswa/index.php'));
exit;
