<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
//VALIDASI METHOD POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('admin/aspirasi/index.php')); exit; }
//VALIDASI CSRF
verifyCsrf();

//INISIALISASI DATABASE
$db = getDB();
$id = (int)($_POST['id'] ?? 0);

//DELETE DATA (CASCADE)
//HAPUS HISTORY STATUS
$db->prepare("DELETE FROM tb_aspirasi_status_history WHERE id_pelaporan=?")->execute([$id]);
//HAPUS ASPIRASI
$db->prepare("DELETE FROM tb_aspirasi WHERE id_pelaporan=?")->execute([$id]);
//HAPUS INPUT ASPIRASI
$db->prepare("DELETE FROM tb_input_aspirasi WHERE id_pelaporan=?")->execute([$id]);

//REDIRECT
setFlash('success', 'Aspirasi berhasil dihapus.');
header('Location: ' . url('admin/aspirasi/index.php'));
exit;
