<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
//VALIDASI METHOD POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('admin/kategori/index.php')); exit; }
//VALIDASI CSRF
verifyCsrf();

//INISIALISASI DATABASE
$db = getDB();
$id = (int)($_POST['id'] ?? 0);

//DELETE RELATED DATA (CASCADE DELETE)
//HAPUS HISTORY STATUS
$db->prepare("DELETE sh FROM tb_aspirasi_status_history sh
    INNER JOIN tb_input_aspirasi ia ON sh.id_pelaporan = ia.id_pelaporan
    WHERE ia.id_kategori = ?")->execute([$id]);
//HAPUS ASPIRASI
$db->prepare("DELETE a FROM tb_aspirasi a
    INNER JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
    WHERE ia.id_kategori = ?")->execute([$id]);
//HAPUS INPUT ASPIRASI
$db->prepare("DELETE FROM tb_input_aspirasi WHERE id_kategori = ?")->execute([$id]);
//HAPUS KATEGORI
$db->prepare("DELETE FROM tb_kategori WHERE id_kategori = ?")->execute([$id]);

//REDIRECT
setFlash('success', 'Kategori berhasil dihapus.');
header('Location: ' . url('admin/kategori/index.php'));
exit;
