<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('mahasiswa');

// Ambil ringkasan pesanan aktif milik mahasiswa ini
$id_user = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pengguna = :id ORDER BY id_pesanan DESC LIMIT 3");
$stmt->execute([':id' => $id_user]);
$pesanan_aktif = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa — PrintKoperasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        /* CSS Tambahan untuk Menu Navigasi */
        .nav-menu {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            background: var(--white);
            padding: 8px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        .nav-menu a {
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            color: var(--gray);
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .nav-menu a:hover {
            background: var(--gray-light);
            color: var(--primary);
        }

        .nav-menu a.active {
            background: var(--primary);
            color: var(--white);
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo">🖨️ PrintKoperasi</div>
        <div class="user-info">
            <span>Halo, <?= $_SESSION['nama_lengkap'] ?> (Mahasiswa)</span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:white;">Logout</a>
        </div>
    </header>

    <div class="container">

        <!-- MENU NAVIGASI MAHASISWA -->
        <div class="nav-menu">
            <a href="dashboard.php" class="active">🏠 Dashboard</a>
            <a href="pesan-cetak.php">📋 Pesan Cetak</a>
            <a href="riwayat.php">📜 Riwayat Transaksi</a>
        </div>

        <!-- CTA Pesan Cetak -->
        <div class="cta-card">
            <div>
                <h2>📋 Pesan Cetak</h2>
                <p>Buat pesanan baru dengan mengunggah berkas</p>
            </div>
            <a href="pesan-cetak.php" class="btn">+ Pesan Sekarang</a>
        </div>

        <!-- Ringkasan Pesanan Aktif -->
        <div class="card">
            <div class="card-header">
                <h2>📊 Status Pesanan Aktif</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Antrian</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan_aktif as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nomor_antrian']) ?></strong></td>
                            <td><?= date('d M Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                            <td>Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                $badge = 'badge-info';
                                if ($row['status_pesanan'] == 'diproses') $badge = 'badge-warning';
                                if ($row['status_pesanan'] == 'selesai') $badge = 'badge-success';
                                if ($row['status_pesanan'] == 'tertunda') $badge = 'badge-danger';
                                ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($row['status_pesanan']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pesanan_aktif)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Belum ada pesanan aktif.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>