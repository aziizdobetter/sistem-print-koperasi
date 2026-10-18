<?php
// auth/check_auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, tendang ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Fungsi untuk mengecek role (Authorization)
function require_role($role)
{
    if ($_SESSION['role'] !== $role) {
        // Jika role tidak sesuai, tampilkan pesan error atau redirect
        http_response_code(403);
        die("Akses Ditolak: Anda tidak memiliki izin untuk halaman ini.");
    }
}
