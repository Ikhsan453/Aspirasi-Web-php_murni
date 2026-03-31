<?php
//SETUP & KONFIGURASI
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

//REDIRECT JIK SUDAH LOGIN
if (isAdminLoggedIn()) {
    header('Location: ' . url('admin/dashboard.php'), true, 302);
    exit();
}

//TANGANI POST REQUEST
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //AMBIL & SANITASI DATA
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    //VALIDASI INPUT
    if (!$username) $errors[] = 'Username wajib diisi.';
    if (!$password) $errors[] = 'Password wajib diisi.';

    //PROSES LOGIN
    if (empty($errors)) {
        try {
            //QUERY DATABASE
            $stmt = getDB()->prepare("SELECT * FROM tb_admin WHERE username = ?");
            $stmt->execute([$username]);
            $adminRow = $stmt->fetch();

            //CEK PASSWORD
            if ($adminRow && password_verify($password, $adminRow['password'])) {
                //SET SESSION
                $_SESSION['admin_id']       = $adminRow['id'];
                $_SESSION['admin_username'] = $adminRow['username'];
                $_SESSION['admin_role']     = $adminRow['role'];
                $_SESSION['login_time']     = time();

                header('Location: ' . url('admin/dashboard.php'), true, 302);
                exit();
            } else {
                $errors[] = 'Username atau password salah.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan database: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Aspirasi Web</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/custom.css') ?>" rel="stylesheet">
    <style>
    .login-container{min-height:100vh;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;padding:2rem 0;}
    .login-card{background:rgba(30,41,59,.95);border:1px solid rgba(51,65,85,.5);border-radius:20px;backdrop-filter:blur(10px);box-shadow:var(--shadow-xl);max-width:450px;width:100%;margin:0 auto;}
    .login-header{background:var(--gradient-accent);padding:2rem;text-align:center;border-radius:20px 20px 0 0;position:relative;overflow:hidden;}
    .login-header::before{content:"";position:absolute;top:-50%;right:-20%;width:200px;height:200px;background:rgba(255,255,255,.1);border-radius:50%;animation:float 6s ease-in-out infinite;}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
    .login-icon{width:80px;height:80px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2rem;color:white;}
    .login-body{padding:2.5rem;}
    </style>
</head>
<body>
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="login-card fade-in">
                    <div class="login-header">
                        <div class="login-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3 class="text-white fw-bold mb-2">Admin Panel</h3>
                        <p class="text-white opacity-75 mb-0">Aspirasi Web</p>
                    </div>
                    <div class="login-body">
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php foreach ($errors as $err): ?>
                                <div><?= e($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <form action="<?= url('admin/login.php') ?>" method="POST" id="loginForm">
                            <div class="mb-4">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?= e($_POST['username'] ?? '') ?>"
                                       placeholder="Masukkan Username" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Masukkan Password" required>
                            </div>
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Admin Panel
                                </button>
                            </div>
                        </form>
                        <a href="<?= url() ?>" class="btn btn-secondary d-block w-100">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    btn.disabled = true;
});
</script>
</body>
</html>
