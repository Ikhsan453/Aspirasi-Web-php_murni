<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('admin/kategori/index.php')); exit; }
verifyCsrf();

$db = getDB();
$id = (int)($_POST['id'] ?? 0);

// Hapus aspirasi terkait dulu
$db->prepare("DELETE sh FROM tb_aspirasi_status_history sh
    INNER JOIN tb_input_aspirasi ia ON sh.id_pelaporan = ia.id_pelaporan
    WHERE ia.id_kategori = ?")->execute([$id]);
$db->prepare("DELETE a FROM tb_aspirasi a
    INNER JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
    WHERE ia.id_kategori = ?")->execute([$id]);
$db->prepare("DELETE FROM tb_input_aspirasi WHERE id_kategori = ?")->execute([$id]);
$db->prepare("DELETE FROM tb_kategori WHERE id_kategori = ?")->execute([$id]);

setFlash('success', 'Kategori berhasil dihapus.');
header('Location: ' . url('admin/kategori/index.php'));
exit;
