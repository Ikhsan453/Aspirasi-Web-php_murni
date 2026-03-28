CREATE DATABASE IF NOT EXISTS aspirasi_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aspirasi_web;

CREATE TABLE IF NOT EXISTS tb_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tb_kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    ket_kategori VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tb_siswa (
    nis VARCHAR(10) PRIMARY KEY,
    kelas VARCHAR(10) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tb_input_aspirasi (
    id_pelaporan INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(10) NOT NULL,
    id_kategori INT NOT NULL,
    lokasi VARCHAR(50) NOT NULL,
    ket TEXT NOT NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nis) REFERENCES tb_siswa(nis) ON DELETE CASCADE,
    FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori)
);

CREATE TABLE IF NOT EXISTS tb_aspirasi (
    id_aspirasi INT AUTO_INCREMENT PRIMARY KEY,
    id_pelaporan INT NOT NULL UNIQUE,
    id_kategori INT NOT NULL,
    ket_kategori VARCHAR(30) NULL,
    status ENUM('Menunggu','Proses','Selesai') DEFAULT 'Menunggu',
    feedback TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelaporan) REFERENCES tb_input_aspirasi(id_pelaporan) ON DELETE CASCADE,
    FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori)
);

CREATE TABLE IF NOT EXISTS tb_aspirasi_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelaporan INT NOT NULL,
    status ENUM('Menunggu','Proses','Selesai') NOT NULL,
    feedback TEXT NULL,
    changed_by VARCHAR(100) DEFAULT 'system',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelaporan) REFERENCES tb_input_aspirasi(id_pelaporan) ON DELETE CASCADE
);

-- Admin default: username=admin, password=admin123
INSERT IGNORE INTO tb_admin (username, password, role) VALUES
('admin', '$2y$10$TKh8H1.PfbuNIcFQ9ynrmuVRNom4L5AAjMZGnSBnHFTd7YSP6C8Gy', 'superadmin');

INSERT IGNORE INTO tb_kategori (id_kategori, ket_kategori) VALUES
(1, 'Kerusakan Fasilitas'),
(2, 'Kebersihan'),
(3, 'Keamanan'),
(4, 'Sarana Belajar'),
(5, 'Lainnya');
