<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('admin');

// --- 1. QUERY INDIKATOR UTAMA ---
// A. Total Pendapatan Keseluruhan (Lunas)
$stmtPendapatan = $pdo->query("SELECT SUM(total_biaya) FROM pesanan WHERE status_pembayaran = 'sudah_dibayar'");
$total_pendapatan = $stmtPendapatan->fetchColumn() ?: 0;

// B. Total Transaksi (Semua Pesanan)
$stmtTotalTrx = $pdo->query("SELECT COUNT(*) FROM pesanan");
$total_transaksi = $stmtTotalTrx->fetchColumn();

// C. Total Pengguna (Mahasiswa yang terdaftar)
$stmtUser = $pdo->query("SELECT COUNT(*) FROM pengguna WHERE role = 'mahasiswa'");
$total_mahasiswa = $stmtUser->fetchColumn();

// D. Total Layanan Aktif
$stmtLayanan = $pdo->query("SELECT COUNT(*) FROM layanan WHERE status_aktif = 1");
$total_layanan = $stmtLayanan->fetchColumn();


// --- 2. DAFTAR TRANSAKSI TERBARU (5 Terakhir) ---
$sqlTerbaru = "SELECT p.*, u.nama_lengkap 
               FROM pesanan p 
               JOIN pengguna u ON p.id_pengguna = u.id_pengguna 
               ORDER BY p.id_pesanan DESC LIMIT 5";
$stmtTerbaru = $pdo->query($sqlTerbaru);
$transaksi_terbaru = $stmtTerbaru->fetchAll();

// --- 3. INFORMASI PENTING: Layanan Paling Banyak Dipesan ---
$sqlPopuler = "SELECT l.nama_layanan, SUM(d.jumlah_lembar) as total_lembar 
               FROM detail_pesanan d 
               JOIN layanan l ON d.id_layanan = l.id_layanan 
               GROUP BY l.id_layanan 
               ORDER BY total_lembar DESC LIMIT 1";
$stmtPopuler = $pdo->query($sqlPopuler);
$layanan_populer = $stmtPopuler->fetch();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin — PrintKoperasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
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
            <span>Halo, <?= $_SESSION['nama_lengkap'] ?> (Admin)</span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:white;">Logout</a>
        </div>
    </header>

    <div class="container">
        <!-- MENU NAVIGASI ADMIN -->
        <div class="nav-menu">
            <a href="dashboard.php" class="active">🏠 Dashboard</a>
            <a href="layanan/index.php">📦 Kelola Layanan</a>
            <a href="laporan.php">📈 Laporan</a>
        </div>

        <!-- INDIKATOR UTAMA (3+1) -->
        <div class="grid-4" style="margin-bottom: 16px;">
            <div class="stat-box" style="border-top: 4px solid var(--success);">
                <div class="stat-value" style="color: var(--success); font-size: 18px;">
                    Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
                </div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid var(--primary);">
                <div class="stat-value"><?= $total_transaksi ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid var(--info);">
                <div class="stat-value"><?= $total_mahasiswa ?></div>
                <div class="stat-label">Mahasiswa Terdaftar</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid var(--accent);">
                <div class="stat-value"><?= $total_layanan ?></div>
                <div class="stat-label">Layanan Aktif</div>
            </div>
        </div>

        <div class="grid-2">
            <!-- DAFTAR TRANSAKSI TERBARU -->
            <div class="card">
                <h2>🕒 Transaksi Terbaru</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Antrian</th>
                            <th>Mahasiswa</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi_terbaru as $trx): ?>
                            <tr>
                                <td><strong><?= $trx['nomor_antrian'] ?></strong></td>
                                <td><?= htmlspecialchars($trx['nama_lengkap']) ?></td>
                                <td>Rp <?= number_format($trx['total_biaya'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $trx['status_pesanan'] == 'selesai' ? 'badge-success' : 'badge-info' ?>">
                                        <?= ucfirst($trx['status_pesanan']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transaksi_terbaru)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">Belum ada transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div style="margin-top:12px; text-align:right;">
                    <a href="laporan.php" class="btn btn-sm btn-secondary">Lihat Semua di Laporan →</a>
                </div>
            </div>

            <!-- INFORMASI PENTING: LAYANAN TERPOPULER -->
            <div class="card">
                <h2>🔥 Layanan Terpopuler</h2>
                <?php if ($layanan_populer): ?>
                    <div style="text-align:center; padding: 24px 0;">
                        <div style="font-size: 48px; margin-bottom: 8px;">🏆</div>
                        <h3 style="font-size: 20px; color: var(--primary); margin-bottom: 4px;">
                            <?= htmlspecialchars($layanan_populer['nama_layanan']) ?>
                        </h3>
                        <p style="color: var(--gray); font-size: 13px;">
                            Total <strong><?= number_format($layanan_populer['total_lembar'], 0, ',', '.') ?></strong> lembar dicetak
                        </p>
                    </div>
                <?php else: ?>
                    <p style="text-align:center; color:var(--gray);">Belum ada data transaksi.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>

</html>