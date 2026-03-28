<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$nis = trim($_GET['nis'] ?? '');
if (!$nis) { echo json_encode(['success'=>false,'message'=>'NIS tidak boleh kosong']); exit; }

try {
    $stmt = getDB()->prepare("SELECT nis, kelas, jurusan FROM tb_siswa WHERE nis = ?");
    $stmt->execute([$nis]);
    $siswa = $stmt->fetch();
    if ($siswa) {
        echo json_encode(['success'=>true,'student'=>['nis'=>$siswa['nis'],'kelas'=>$siswa['kelas'],'jurusan'=>$siswa['jurusan']]]);
    } else {
        echo json_encode(['success'=>false,'message'=>'NIS tidak ditemukan dalam database siswa']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Terjadi kesalahan saat memverifikasi NIS']);
}
