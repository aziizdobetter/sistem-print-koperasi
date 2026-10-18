<?php
require_once '../../../config/config.php';
require_once '../../../auth/check_auth.php';
require_once '../../../config/database.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_layanan = trim($_POST['nama_layanan']);
    $deskripsi = trim($_POST['deskripsi']);
    $satuan = trim($_POST['satuan']);
    $status_aktif = $_POST['status_aktif'];

    // 1. Validasi Required
    if (empty($nama_layanan) || empty($satuan)) {
        header("Location: create.php?error=Nama layanan dan satuan wajib diisi!");
        exit;
    }

    // 2. Validasi Duplikat (Nama layanan tidak boleh sama)
    $stmt = $pdo->prepare("SELECT id_layanan FROM layanan WHERE nama_layanan = :nama");
    $stmt->execute([':nama' => $nama_layanan]);
    if ($stmt->fetch()) {
        header("Location: create.php?error=Nama layanan sudah ada, gunakan nama lain!");
        exit;
    }

    // 3. Simpan ke Database (Create)
    try {
        $sql = "INSERT INTO layanan (nama_layanan, deskripsi, satuan, status_aktif) VALUES (:nama, :deskripsi, :satuan, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama' => $nama_layanan,
            ':deskripsi' => $deskripsi,
            ':satuan' => $satuan,
            ':status' => $status_aktif
        ]);

        header("Location: index.php?msg=Data layanan berhasil ditambahkan.");
        exit;
    } catch (PDOException $e) {
        header("Location: create.php?error=Gagal menyimpan data: " . $e->getMessage());
        exit;
    }
}
