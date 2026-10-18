<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=Username dan password wajib diisi!");
        exit;
    }

    try {
        // Ambil data user berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Verifikasi password
        // CATATAN: Jika di database Anda masih menggunakan teks biasa (misal 'hash_pass_1'), 
        // sementara ganti pengecekan menjadi: if ($user && $password === $user['password'])
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id_pengguna'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'mahasiswa') {
                header("Location: " . BASE_URL . "public/mahasiswa/dashboard.php");
            } elseif ($user['role'] === 'petugas') {
                header("Location: " . BASE_URL . "public/petugas/dashboard.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "public/admin/dashboard.php");
            }
            exit;
        } else {
            header("Location: login.php?error=Username atau password salah!");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=Terjadi kesalahan sistem: " . $e->getMessage());
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
