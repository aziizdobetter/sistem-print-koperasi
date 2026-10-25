<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('admin');

// Tangkap filter periode (Default: Bulan ini)
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$nama_bulan = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];

// Query Data Transaksi berdasarkan periode
// Kita ambil semua transaksi (termasuk yang belum lunas) untuk evaluasi
$sql = "SELECT p.*, u.nama_lengkap 
        FROM pesanan p 
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna 
        WHERE MONTH(p.tanggal_pesan) = :bulan AND YEAR(p.tanggal_pesan) = :tahun
        ORDER BY p.tanggal_pesan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':bulan' => $bulan, ':tahun' => $tahun]);
$transaksi = $stmt->fetchAll();

// Hitung Ringkasan (Total Pendapatan & Volume)
$total_pendapatan = 0;
$total_lunas = 0;
$total_transaksi = count($transaksi);

foreach ($transaksi as $trx) {
    if ($trx['status_pembayaran'] === 'sudah_dibayar') {
        $total_pendapatan += $trx['total_biaya'];
        $total_lunas++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pendapatan — PrintKoperasi</title>
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

        /* CSS untuk Print (Cetak) */
        @media print {

            .header,
            .nav-menu,
            .no-print {
                display: none !important;
            }

            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .card {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            body {
                font-size: 12px;
                background: white;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table th,
            table td {
                border: 1px solid #000;
                padding: 8px;
            }
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
        <div class="nav-menu no-print">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="layanan/index.php">📦 Kelola Layanan</a>
            <a href="laporan.php" class="active">📈 Laporan</a>
        </div>

        <!-- Filter Periode (Hanya muncul di layar, tidak di cetakan) -->
        <div class="card no-print">
            <h2>Filter Laporan</h2>
            <form method="GET" action="" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Bulan</label>
                    <select name="bulan" style="padding:8px; border:1px solid var(--border); border-radius:8px;">
                        <?php foreach ($nama_bulan as $num => $nm): ?>
                            <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $nm ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Tahun</label>
                    <select name="tahun" style="padding:8px; border:1px solid var(--border); border-radius:8px;">
                        <?php for ($i = 2024; $i <= 2027; $i++): ?>
                            <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <button type="button" onclick="window.print()" class="btn btn-secondary">🖨️ Cetak Laporan</button>
            </form>
        </div>

        <!-- AREA LAPORAN -->
        <div class="card">
            <div style="text-align:center; margin-bottom:24px;">
                <h2 style="font-size:20px; margin-bottom:4px;">LAPORAN PENDAPATAN KOPERASI PRINT/FOTOKOPI</h2>
                <p style="color:var(--gray);">Periode: <?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
            </div>

            <!-- Ringkasan -->
            <div class="grid-3" style="margin-bottom:16px;">
                <div class="stat-box" style="border:1px solid var(--border);">
                    <div class="stat-value"><?= $total_transaksi ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
                <div class="stat-box" style="border:1px solid var(--border);">
                    <div class="stat-value"><?= $total_lunas ?></div>
                    <div class="stat-label">Transaksi Lunas</div>
                </div>
                <div class="stat-box" style="border:1px solid var(--border); background:var(--primary-light);">
                    <div class="stat-value" style="color:var(--success);">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Pendapatan (Lunas)</div>
                </div>
            </div>

            <!-- Tabel Data -->
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Antrian</th>
                        <th>Nama Mahasiswa</th>
                        <th>Total Biaya</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($transaksi as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                            <td><strong><?= $row['nomor_antrian'] ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td>Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($row['status_pembayaran'] === 'sudah_dibayar'): ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($transaksi)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Tidak ada transaksi pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 24px; text-align: right; font-size: 12px; color: var(--gray);">
                Dicetak pada: <?= date('d/m/Y H:i') ?>
            </div>
        </div>
    </div>
</body>

</html>