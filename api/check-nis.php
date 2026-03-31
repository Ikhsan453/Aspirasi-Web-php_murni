<?php
//SETUP API JSON
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

//AMBIL & VALIDASI NIS
$nis = trim($_GET['nis'] ?? '');
if (!$nis) { echo json_encode(['success'=>false,'message'=>'NIS tidak boleh kosong']); exit; }

//QUERY & RETURN JSON
try {
    //QUERY DATABASE
    $stmt = getDB()->prepare("SELECT nis, kelas, jurusan FROM tb_siswa WHERE nis = ?");
    $stmt->execute([$nis]);
    $siswa = $stmt->fetch();
    //CEK HASIL
    if ($siswa) {
        //RETURN SUCCESS
        echo json_encode(['success'=>true,'student'=>['nis'=>$siswa['nis'],'kelas'=>$siswa['kelas'],'jurusan'=>$siswa['jurusan']]]);
    } else {
        //RETURN ERROR NIS TIDAK DITEMUKAN
        echo json_encode(['success'=>false,'message'=>'NIS tidak ditemukan dalam database siswa']);
    }
} catch (Exception $e) {
    //RETURN ERROR DATABASE
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Terjadi kesalahan saat memverifikasi NIS']);
}
