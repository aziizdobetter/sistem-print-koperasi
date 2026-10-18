<?php
require_once '../../../config/config.php';
require_once '../../../auth/check_auth.php';
require_once '../../../config/database.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id_layanan'];
    $nama_layanan = trim($_POST['nama_layanan']);
    $deskripsi = trim($_POST['deskripsi']);
    $satuan = trim($_POST['satuan']);
    $status_aktif = $_POST['status_aktif'];

    if (empty($nama_layanan) || empty($satuan)) {
        header("Location: edit.php?id=$id&error=Nama layanan dan satuan wajib diisi!");
        exit;
    }

    // Validasi duplikat (kecuali dirinya sendiri)
    $stmt = $pdo->prepare("SELECT id_layanan FROM layanan WHERE nama_layanan = :nama AND id_layanan != :id");
    $stmt->execute([':nama' => $nama_layanan, ':id' => $id]);
    if ($stmt->fetch()) {
        header("Location: edit.php?id=$id&error=Nama layanan sudah digunakan!");
        exit;
    }

    try {
        $sql = "UPDATE layanan SET nama_layanan = :nama, deskripsi = :deskripsi, satuan = :satuan, status_aktif = :status WHERE id_layanan = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama' => $nama_layanan,
            ':deskripsi' => $deskripsi,
            ':satuan' => $satuan,
            ':status' => $status_aktif,
            ':id' => $id
        ]);

        header("Location: index.php?msg=Data layanan berhasil diperbarui.");
        exit;
    } catch (PDOException $e) {
        header("Location: edit.php?id=$id&error=Gagal memperbarui data: " . $e->getMessage());
        exit;
    }
}
