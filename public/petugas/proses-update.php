<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('petugas');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = (int)$_POST['id_pesanan'];
    $status_lama = $_POST['status_lama'];
    $status_baru = $_POST['status_baru'];
    $keterangan = trim($_POST['keterangan'] ?? '');
    $id_petugas = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Ambil data pesanan terbaru untuk validasi
        $stmtCek = $pdo->prepare("SELECT status_pesanan, status_pembayaran, total_biaya FROM pesanan WHERE id_pesanan = :id");
        $stmtCek->execute([':id' => $id_pesanan]);
        $pesanan_db = $stmtCek->fetch();

        if (!$pesanan_db) {
            throw new Exception("Pesanan tidak ditemukan.");
        }

        // 2. BR-13: Pesanan yang sudah diambil tidak bisa diubah
        if ($pesanan_db['status_pesanan'] === 'diambil') {
            throw new Exception("Pesanan sudah diambil, tidak bisa diubah.");
        }

        // =====================================================================
        // VALIDASI BR-06 vs BR-14
        // =====================================================================
        if ($status_baru === 'tertunda') {
            // BR-14: Jika status baru adalah "tertunda", langsung izinkan.
            // Tidak perlu validasi urutan.
        } else {
            // BR-06: Validasi urutan status normal
            $urutan_valid = false;

            if ($pesanan_db['status_pesanan'] === 'tertunda' && $status_baru === 'diproses') {
                $urutan_valid = true; // Lanjutkan dari tertunda
            } elseif ($pesanan_db['status_pesanan'] === 'menunggu' && $status_baru === 'diproses') {
                $urutan_valid = true;
            } elseif ($pesanan_db['status_pesanan'] === 'diproses' && $status_baru === 'selesai') {
                $urutan_valid = true;
            } elseif ($pesanan_db['status_pesanan'] === 'selesai' && $status_baru === 'diambil') {
                $urutan_valid = true;
            }

            if (!$urutan_valid) {
                throw new Exception("Status tidak valid. Urutan harus sesuai (Menunggu -> Diproses -> Selesai -> Diambil).");
            }
        }

        // =====================================================================
        // VALIDASI BR-08: Pembayaran (Hanya jika akan diambil)
        // =====================================================================
        if ($status_baru === 'diambil') {
            if ($pesanan_db['status_pembayaran'] !== 'sudah_dibayar') {
                throw new Exception("Pesanan belum dibayar! Tandai pembayaran terlebih dahulu.");
            }
        }

        // =====================================================================
        // PROSES UPDATE KE DATABASE
        // =====================================================================

        // A. Update status di tabel pesanan
        $stmtUpdate = $pdo->prepare("UPDATE pesanan SET status_pesanan = :status_baru WHERE id_pesanan = :id");
        $stmtUpdate->execute([':status_baru' => $status_baru, ':id' => $id_pesanan]);

        // B. Update tanggal jika selesai/diambil
        if ($status_baru === 'selesai') {
            $pdo->prepare("UPDATE pesanan SET tanggal_selesai = NOW() WHERE id_pesanan = :id")->execute([':id' => $id_pesanan]);
        } elseif ($status_baru === 'diambil') {
            $pdo->prepare("UPDATE pesanan SET tanggal_diambil = NOW() WHERE id_pesanan = :id")->execute([':id' => $id_pesanan]);
        }

        // C. Proses Pembayaran (BR-08)
        if (isset($_POST['sudah_dibayar']) && $_POST['sudah_dibayar'] == 1) {
            // Update status pembayaran di tabel pesanan
            $pdo->prepare("UPDATE pesanan SET status_pembayaran = 'sudah_dibayar' WHERE id_pesanan = :id")->execute([':id' => $id_pesanan]);

            // Insert ke tabel pembayaran
            // Cek dulu apakah sudah ada record pembayaran (mencegah duplikasi UNIQUE)
            $stmtCekBayar = $pdo->prepare("SELECT id_pembayaran FROM pembayaran WHERE id_pesanan = :id");
            $stmtCekBayar->execute([':id' => $id_pesanan]);

            if (!$stmtCekBayar->fetch()) {
                $stmtInsertBayar = $pdo->prepare("INSERT INTO pembayaran (id_pesanan, jumlah_bayar, status_pembayaran, id_petugas) VALUES (:id_pesanan, :jumlah, 'sudah_dibayar', :petugas)");
                $stmtInsertBayar->execute([
                    ':id_pesanan' => $id_pesanan,
                    ':jumlah' => $pesanan_db['total_biaya'],
                    ':petugas' => $id_petugas
                ]);
            }
        }

        // D. Catat ke tabel riwayat_status (BR-15)
        $stmtRiwayat = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, id_pengguna, keterangan) VALUES (:id, :lama, :baru, :user, :ket)");
        $stmtRiwayat->execute([
            ':id' => $id_pesanan,
            ':lama' => $pesanan_db['status_pesanan'], // Gunakan status dari DB yang paling akurat
            ':baru' => $status_baru,
            ':user' => $id_petugas,
            ':ket' => $keterangan
        ]);

        $pdo->commit();

        // Redirect dengan pesan sukses
        header("Location: dashboard.php?msg=Status pesanan berhasil diperbarui menjadi " . ucfirst($status_baru));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        // Tangkap pesan error dari validasi (BR) atau dari database
        header("Location: update-status.php?id=" . $id_pesanan . "&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}
