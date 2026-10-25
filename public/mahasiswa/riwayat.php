<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('mahasiswa');

$id_user = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_bayar = isset($_GET['bayar']) ? $_GET['bayar'] : '';

// Query dasar: Hanya ambil pesanan milik mahasiswa yang login
$sql = "SELECT * FROM pesanan WHERE id_pengguna = :id_user";
$params = [':id_user' => $id_user];

// Filter berdasarkan nomor antrian
if (!empty($search)) {
    $sql .= " AND nomor_antrian LIKE :search";
    $params[':search'] = "%$search%";
}

// Filter berdasarkan status pembayaran
if (!empty($filter_bayar)) {
    $sql .= " AND status_pembayaran = :bayar";
    $params[':bayar'] = $filter_bayar;
}

$sql .= " ORDER BY tanggal_pesan DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi — PrintKoperasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <header class="header">
        <div class="logo">🖨️ PrintKoperasi</div>
        <div class="user-info">
            <span>Halo, <?= $_SESSION['nama_lengkap'] ?></span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:white;">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php">🏠 Dashboard</a> &gt; 📜 Riwayat Transaksi
        </div>

        <div class="card">
            <div class="card-header" style="flex-direction: column; align-items: flex-start; gap: 12px;">
                <h2 style="margin-bottom: 0;">Riwayat Transaksi Saya</h2>

                <!-- FORM FILTER -->
                <form method="GET" action="" style="display: flex; gap: 8px; width: 100%; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Cari nomor antrian..."
                        value="<?= htmlspecialchars($search) ?>"
                        style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;">

                    <select name="bayar" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Semua Pembayaran</option>
                        <option value="belum_dibayar" <?= $filter_bayar == 'belum_dibayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                        <option value="sudah_dibayar" <?= $filter_bayar == 'sudah_dibayar' ? 'selected' : '' ?>>Sudah Dibayar</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="riwayat.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Antrian</th>
                        <th>Tanggal</th>
                        <th>Total Biaya</th>
                        <th>Status Pesanan</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nomor_antrian']) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                            <td>Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge <?= $row['status_pesanan'] == 'selesai' || $row['status_pesanan'] == 'diambil' ? 'badge-success' : 'badge-info' ?>">
                                    <?= ucfirst($row['status_pesanan']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status_pembayaran'] === 'sudah_dibayar'): ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($riwayat)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Tidak ada data transaksi yang cocok dengan pencarian.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>