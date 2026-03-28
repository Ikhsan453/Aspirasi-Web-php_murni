<?php

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function getAdminUser(): ?array {
    if (!isAdminLoggedIn()) return null;
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'role'     => $_SESSION['admin_role'] ?? 'admin',
    ];
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash(string $type): ?string {
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

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

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function strLimit(string $str, int $limit = 30): string {
    return mb_strlen($str) > $limit ? mb_substr($str, 0, $limit) . '...' : $str;
}

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

function paginationLinks(array $p): string {
    if ($p['total_pages'] <= 1) return '';
    $html = '<nav><ul class="pagination justify-content-center flex-wrap">';
    if ($p['current'] > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . ($p['current'] - 1) . '">&laquo;</a></li>';
    }
    for ($i = 1; $i <= $p['total_pages']; $i++) {
        $active = $i === $p['current'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $p['base_url'] . '&page=' . $i . '">' . $i . '</a></li>';
    }
    if ($p['current'] < $p['total_pages']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . ($p['current'] + 1) . '">&raquo;</a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}
