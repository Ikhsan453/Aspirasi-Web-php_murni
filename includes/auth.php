<?php
//==================== HELPER FUNCTIONS ====================

//FUNCTION: CEK STATUS LOGIN ADMIN
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

//FUNCTION: REDIRECT JIK TIDAK LOGIN
function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

//FUNCTION: GET DATA ADMIN LOGIN
function getAdminUser(): ?array {
    if (!isAdminLoggedIn()) return null;
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'role'     => $_SESSION['admin_role'] ?? 'admin',
    ];
}

//FUNCTION: SET FLASH MESSAGE
function setFlash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

//FUNCTION: GET FLASH MESSAGE
function getFlash(string $type): ?string {
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

//FUNCTION: GENERATE CSRF TOKEN
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

//FUNCTION: VERIFIKASI CSRF TOKEN
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    // Jika session token kosong, generate baru dan lanjutkan (jangan block)
    if (empty($sessionToken)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return;
    }
    if (!hash_equals($sessionToken, $token)) {
        http_response_code(403);
        die('CSRF token tidak valid. <a href="javascript:history.back()">Kembali</a>');
    }
}

//FUNCTION: HTML ESCAPE OUTPUT
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//FUNCTION: BATASI PANJANG STRING
function strLimit(string $str, int $limit = 30): string {
    return mb_strlen($str) > $limit ? mb_substr($str, 0, $limit) . '...' : $str;
}

//FUNCTION: HITUNG PAGINATION
function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): array {
    $totalPages = (int) ceil($total / max(1, $perPage));
    $offset     = ($currentPage - 1) * $perPage;
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => max(0, $offset),
        'base_url'    => $baseUrl,
    ];
}

//FUNCTION: GENERATE PAGINATION LINKS
function paginationLinks(array $p): string {
    if ($p['total_pages'] <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center flex-wrap">';
    
    // Previous button
    if ($p['current'] > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . ($p['current'] - 1) . '">&laquo;</a></li>';
    }
    
    // Calculate range based on per_page value
    // Group halaman sesuai per_page (5, 10, 25, dll)
    $perPage = $p['per_page'] ?? 10;
    $groupSize = $perPage; // Jumlah halaman yang ditampilkan per grup
    
    // Tentukan group mana current page berada
    $groupNum = (int) ceil($p['current'] / $groupSize);
    $start = ($groupNum - 1) * $groupSize + 1;
    $end = min($groupSize * $groupNum, $p['total_pages']);
    
    // Show ellipsis and first page if needed
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Show current group of pages
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $p['current'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $p['base_url'] . '&page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Show ellipsis and last page if needed
    if ($end < $p['total_pages']) {
        if ($end < $p['total_pages'] - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . $p['total_pages'] . '">' . $p['total_pages'] . '</a></li>';
    }
    
    // Next button
    if ($p['current'] < $p['total_pages']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . ($p['current'] + 1) . '">&raquo;</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}
