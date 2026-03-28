<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('admin/aspirasi/index.php')); exit; }
verifyCsrf();

$db = getDB();
$id = (int)($_POST['id'] ?? 0);

$db->prepare("DELETE FROM tb_aspirasi_status_history WHERE id_pelaporan=?")->execute([$id]);
$db->prepare("DELETE FROM tb_aspirasi WHERE id_pelaporan=?")->execute([$id]);
$db->prepare("DELETE FROM tb_input_aspirasi WHERE id_pelaporan=?")->execute([$id]);

setFlash('success', 'Aspirasi berhasil dihapus.');
header('Location: ' . url('admin/aspirasi/index.php'));
exit;
