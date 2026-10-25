<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('petugas');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = (int)$_POST['id_pesanan'];
    $status_lama = $_POST['status_lama'];
    $status_baru = $_POST['status_baru'];
    $keterangan = trim($_POST['keterangan']);
    $id_petugas = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Update status di tabel pesanan
        $stmtUpdate = $pdo->prepare("UPDATE pesanan SET status_pesanan = :status_baru WHERE id_pesanan = :id");
        $stmtUpdate->execute([
            ':status_baru' => $status_baru,
            ':id' => $id_pesanan
        ]);

        // 2. Update tanggal_selesai / tanggal_diambil (opsional, untuk pelaporan akurat)
        if ($status_baru === 'selesai') {
            $pdo->prepare("UPDATE pesanan SET tanggal_selesai = NOW() WHERE id_pesanan = :id")->execute([':id' => $id_pesanan]);
        } elseif ($status_baru === 'diambil') {
            $pdo->prepare("UPDATE pesanan SET tanggal_diambil = NOW() WHERE id_pesanan = :id")->execute([':id' => $id_pesanan]);
        }

        // 3. Catat ke tabel riwayat_status (Audit Trail - BR-15)
        $stmtRiwayat = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, id_pengguna, keterangan) VALUES (:id, :lama, :baru, :user, :ket)");
        $stmtRiwayat->execute([
            ':id' => $id_pesanan,
            ':lama' => $status_lama,
            ':baru' => $status_baru,
            ':user' => $id_petugas,
            ':ket' => $keterangan
        ]);

        $pdo->commit();
        header("Location: dashboard.php?msg=Status pesanan berhasil diperbarui menjadi " . ucfirst($status_baru));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: update-status.php?id=" . $id_pesanan . "&error=Gagal update: " . $e->getMessage());
        exit;
    }
}
