# 📚 README - Sistem Aspirasi Web

## 🎯 Tentang Project

**Sistem Aspirasi Web** adalah aplikasi web untuk mengelola aspirasi/masukan siswa mengenai kondisi sarana dan prasarana sekolah. Sistem ini dilengkapi dengan:

- ✓ Public Page untuk siswa mengajukan aspirasi
- ✓ Admin Panel untuk manage aspirasi
- ✓ Status tracking aspirasi
- ✓ Database MySQL dengan struktur yang terorganisir
- ✓ Authentication system dengan session

---

## 📁 Struktur Project

```
Aspirasi_Web/
├── admin/
│   ├── index.php              Main hub untuk admin tools
│   ├── login.php              Form login admin
│   ├── logout.php             Logout handler
│   ├── dashboard.php          Dashboard (protected)
│   ├── docs.php               API documentation
│   ├── integration.php        Integration guide
│   ├── debug-login.php        Debug tools
│   └── setup.php              Password setup
├── api/
│   └── check-nis.php          API untuk check NIS student
├── aspirasi/
│   ├── create.php             Form buat aspirasi baru
│   ├── status.php             Cek status aspirasi
│   └── success.php            Success page
├── config/
│   ├── db.php                 Database connection
│   ├── init.sql               Database schema
│   └── seed-admins.php        Admin seeder
├── includes/
│   ├── auth.php               Session & helper functions
│   ├── header_admin.php       Admin page header
│   ├── footer_admin.php       Admin page footer
│   ├── header_app.php         Public page header
│   └── footer_app.php         Public page footer
├── assets/
│   ├── css/
│   │   └── custom.css         Custom styling
│   └── js/
│       └── main.js            JavaScript functions
├── index.php                  Public home page
└── README.md                  This file
```

---

## 🚀 Quick Start

### 1. Requirements
- PHP 7.4+
- MySQL/MariaDB 5.7+
- XAMPP atau web server dengan PHP

### 2. Installation

#### A. Copy Project
```bash
# Copy folder Aspirasi_Web ke htdocs
cp -r Aspirasi_Web /path/to/htdocs/
```

#### B. Setup Database
```bash
# MySQL sudah running, import schema
mysql -u root < config/init.sql
```

#### C. Seed Admin Accounts
```bash
# Jalankan seeder untuk create 3 akun admin
php config/seed-admins.php
```

### 3. Access Application

**Public Page:**
```
http://localhost/Aspirasi_Web/
```

**Admin Panel Hub:**
```
http://localhost/Aspirasi_Web/admin/
```

**Admin Login:**
```
http://localhost/Aspirasi_Web/admin/login.php
```

---

## 👥 Admin Accounts

Setelah menjalankan `seed-admins.php`, berikut akun admin yang tersedia:

| Username | Password | Role | Deskripsi |
|----------|----------|------|-----------|
| **admin** | admin123 | superadmin | Full Access - Super Administrator |
| **admin1** | admin123 | admin | Standard Access - Administrator |
| **admin2** | admin123 | admin | Standard Access - Administrator |
| **admin3** | admin123 | admin | Standard Access - Administrator |

> ⚠️ **PENTING:** Ganti password setelah login pertama kali menggunakan [Setup Password](#setup-password)

---

## 🔐 Login & Authentication

### Login Flow

```
User Input (username, password)
         ↓
Verify Session & CSRF Token
         ↓
Query Database
         ↓
Password Verification (bcrypt)
         ↓
Set Session Variables
         ↓
Redirect to Dashboard
```

### Session Functions (includes/auth.php)

```php
// Check if user logged in
isAdminLoggedIn(): bool

// Get current admin info
getAdminUser(): array  // ['id', 'username', 'role']

// Check login, redirect if not
requireAdmin(): void

// Set flash message
setFlash(string $type, string $message): void

// Get & delete flash message
getFlash(string $type): ?string
```

---

## 🔧 Setup Password

### Mengubah Password Admin

**URL:** `http://localhost/Aspirasi_Web/admin/setup.php`

Halaman ini memungkinkan:
1. **Reset Password** - Reset ke default `admin123`
2. **Set Password Baru** - Set custom password dengan validasi

---

## 📝 Database Schema

### Tables:

#### 1. tb_admin
```sql
CREATE TABLE tb_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. tb_siswa
```sql
CREATE TABLE tb_siswa (
    nis VARCHAR(10) PRIMARY KEY,
    kelas VARCHAR(10) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    password VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 3. tb_kategori
```sql
CREATE TABLE tb_kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    ket_kategori VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 4. tb_input_aspirasi
```sql
CREATE TABLE tb_input_aspirasi (
    id_pelaporan INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(10) NOT NULL,
    id_kategori INT NOT NULL,
    lokasi VARCHAR(50) NOT NULL,
    ket TEXT NOT NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nis) REFERENCES tb_siswa(nis),
    FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori)
);
```

#### 5. tb_aspirasi
```sql
CREATE TABLE tb_aspirasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelaporan INT UNIQUE NOT NULL,
    id_kategori INT NOT NULL,
    status ENUM('Menunggu','Proses','Selesai') DEFAULT 'Menunggu',
    feedback TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelaporan) REFERENCES tb_input_aspirasi(id_pelaporan),
    FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori)
);
```

#### 6. tb_aspirasi_status_history
```sql
CREATE TABLE tb_aspirasi_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelaporan INT NOT NULL,
    status ENUM('Menunggu','Proses','Selesai') NOT NULL,
    feedback TEXT NULL,
    changed_by VARCHAR(100) DEFAULT 'system',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelaporan) REFERENCES tb_input_aspirasi(id_pelaporan)
);
```

---

## 💻 Mengintegrasikan Kode

### Template untuk Page Admin Baru

Setiap halaman admin harus mengikuti template ini:

```php
<?php
// 1. Include config & auth
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// 2. Set page title
$pageTitle = 'Nama Page';

// 3. CHECK LOGIN (WAJIB di awal)
if (!isAdminLoggedIn()) {
    header('Location: ' . url('admin/login.php'));
    exit;
}

// 4. Get database & admin info
$db = getDB();
$admin = getAdminUser();

// 5. Include header
require_once __DIR__ . '/../includes/header_admin.php';
?>

<!-- Your Page Content Here -->

<?php
// 6. Include footer
require_once __DIR__ . '/../includes/footer_admin.php';
?>
```

### Contoh: Halaman Kelola Siswa

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Kelola Siswa';

if (!isAdminLoggedIn()) {
    header('Location: ' . url('admin/login.php'));
    exit;
}

$db = getDB();
$admin = getAdminUser();

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = $_POST['nis'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    
    try {
        $stmt = $db->prepare("INSERT INTO tb_siswa (nis, kelas, jurusan) VALUES (?, ?, ?)");
        $stmt->execute([$nis, $kelas, $_POST['jurusan']]);
        setFlash('success', 'Siswa berhasil ditambahkan');
        header('Location: ' . url('admin/siswa.php'));
        exit;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$students = $db->query("SELECT * FROM tb_siswa")->fetchAll();

require_once __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <h2>Kelola Siswa</h2>
    
    <!-- Form Tambah -->
    <form method="POST" class="card p-3 mb-4">
        <input type="text" name="nis" placeholder="NIS" required>
        <input type="text" name="kelas" placeholder="Kelas" required>
        <input type="text" name="jurusan" placeholder="Jurusan" required>
        <button type="submit" class="btn btn-primary">Tambah</button>
    </form>
    
    <!-- List Siswa -->
    <table class="table">
        <thead>
            <tr>
                <th>NIS</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><?= e($s['nis']) ?></td>
                <td><?= e($s['kelas']) ?></td>
                <td><?= e($s['jurusan']) ?></td>
                <td>
                    <a href="edit.php?nis=<?= $s['nis'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete.php?nis=<?= $s['nis'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
```

---

## 📚 Helper Functions

### Database

```php
// Get database connection
$db = getDB();

// Query
$result = $db->query("SELECT * FROM tb_siswa")->fetchAll();

// Prepared statement
$stmt = $db->prepare("SELECT * FROM tb_siswa WHERE nis = ?");
$stmt->execute([$nis]);
$siswa = $stmt->fetch();
```

### URL & Navigation

```php
// Generate URL
url('admin/page.php')  // /Aspirasi_Web/admin/page.php

// Redirect
header('Location: ' . url('admin/dashboard.php'));
```

### Session & Auth

```php
// Check login
if (isAdminLoggedIn()) { ... }

// Get user info
$admin = getAdminUser();  // ['id', 'username', 'role']

// Flash messages
setFlash('success', 'Berhasil!');
setFlash('error', 'Error!');

$msg = getFlash('success');  // Get & delete
```

### Security

```php
// Escape HTML
echo e($user_input);  // htmlspecialchars()

// Password hash
$hash = password_hash($password, PASSWORD_BCRYPT);
password_verify($input, $hash);  // true/false
```

---

## 🐛 Debugging & Testing

### Debug Tools

| Tool | URL | Fungsi |
|------|-----|--------|
| **Admin Hub** | `/admin/` | Main access point |
| **Debug Login** | `/admin/debug-login.php` | Check session & database |
| **Test Submit** | `/admin/submit-test.php` | Test form submission |
| **API Docs** | `/admin/docs.php` | Login flow documentation |
| **Integration Guide** | `/admin/integration.php` | How to integrate code |

---

## 🔄 Seeding & Initialization

### Re-seed Admin Accounts

```bash
# Jalankan lagi untuk re-seed
php config/seed-admins.php
```

**Output:**
```
=== Admin Seeder ===

Inserting admin accounts...
✓ Updated: admin (Super Admin - Full Access)
✓ Updated: admin1 (Admin 1 - Standard Access)
✓ Updated: admin2 (Admin 2 - Standard Access)

=== SUMMARY ===
Inserted: 0
Updated: 3

✓ Seeding completed successfully!
```

---

## 🆘 Troubleshooting

### Problem: Login gagal

**Solusi:**
1. Cek credentials di database:
   ```sql
   SELECT * FROM tb_admin;
   ```

2. Test password verify:
   - Ke `/admin/debug-login.php`
   - Jalankan test submit

3. Reset password:
   - Ke `/admin/setup.php`
   - Click "Reset Password to Default"

### Problem: Token keamanan tidak valid

**Solusi:**
1. Clear browser cookies
2. Refresh halaman login
3. Cek session save path: `/admin/debug-login.php`

### Problem: Tidak bisa akses admin page

**Solusi:**
1. Verifikasi login di `/admin/login.php`
2. Check session variables di `/admin/debug-login.php`
3. Pastikan `requireAdmin()` ada di awal page

---

## 📋 Checklist untuk Development

- [ ] Aplikasi sudah berjalan di `http://localhost/Aspirasi_Web/`
- [ ] Admin dapat login dengan credentials yang ada
- [ ] Database sudah ter-seed dengan 3 akun admin
- [ ] Flash messages working (success/error)
- [ ] CSRF protection aktif
- [ ] Session timeout berjalan
- [ ] Password hashing menggunakan bcrypt
- [ ] All pages require login check
- [ ] URL helper function digunakan di semua link
- [ ] Error handling implement properly

---

## 📞 Support

Untuk bantuan lebih lanjut, gunakan **Debug Tools** yang tersedia di Admin Panel Hub.

---

## 📝 Notes

- **Password Default:** `admin123`
- **Database:** `aspirasi_web`
- **MySQL User:** `root` (tanpa password)
- **PHP Version:** 7.4+
- **Session Timeout:** Browser close (default PHP)

---

**Last Updated:** March 28, 2026
