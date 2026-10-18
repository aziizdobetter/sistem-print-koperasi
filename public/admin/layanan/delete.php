<?php
require_once '../../../config/config.php';
require_once '../../../auth/check_auth.php';
require_once '../../../config/database.php';
require_role('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Cek apakah layanan ini sudah dipakai di tabel tarif atau detail_pesanan
        // (Menerapkan Business Rule: Data master yang sudah berelasi tidak boleh dihapus sembarangan)
        $stmtCek = $pdo->prepare("SELECT COUNT(*) FROM tarif WHERE id_layanan = :id");
        $stmtCek->execute([':id' => $id]);
        if ($stmtCek->fetchColumn() > 0) {
            header("Location: index.php?msg=Gagal: Layanan ini sudah memiliki tarif terkait.");
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM layanan WHERE id_layanan = :id");
        $stmt->execute([':id' => $id]);
        header("Location: index.php?msg=Data layanan berhasil dihapus.");
        exit;
    } catch (PDOException $e) {
        header("Location: index.php?msg=Gagal menghapus data: " . $e->getMessage());
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
