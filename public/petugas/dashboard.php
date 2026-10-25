<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('petugas');

// Tangkap parameter pencarian dan filter dari URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Bangun query dasar
$sql = "SELECT p.*, u.nama_lengkap, b.nama_file 
        FROM pesanan p 
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna 
        JOIN berkas b ON p.id_berkas = b.id_berkas
        WHERE 1=1";

$params = [];

// Jika ada pencarian (berdasarkan nama mahasiswa atau nomor antrian)
if (!empty($search)) {
    // PERBAIKAN: Gunakan nama parameter yang berbeda untuk setiap placeholder
    $sql .= " AND (u.nama_lengkap LIKE :search_nama OR p.nomor_antrian LIKE :search_antrian)";
    $params[':search_nama'] = "%$search%";
    $params[':search_antrian'] = "%$search%";
}

// Jika ada filter status
if (!empty($filter_status)) {
    $sql .= " AND p.status_pesanan = :status";
    $params[':status'] = $filter_status;
} else {
    // Jika tidak ada filter, tampilkan semua kecuali yang sudah diambil
    $sql .= " AND p.status_pesanan IN ('menunggu', 'diproses', 'selesai', 'tertunda')";
}

$sql .= " ORDER BY p.tanggal_pesan ASC";

// Eksekusi query
$stmt = $pdo->prepare($sql);
$stmt->execute($params); // Sekarang jumlah parameter sudah pas
$pesanan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas — PrintKoperasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <header class="header">
        <div class="logo">🖨️ PrintKoperasi</div>
        <div class="user-info">
            <span>Halo, <?= $_SESSION['nama_lengkap'] ?> (Petugas)</span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:white;">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>📋 Daftar Pesanan Masuk (FIFO)</h2>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div style="background:#d4edda; color:#28a745; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div style="background:#f8d7da; color:#dc3545; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <div class="card-header" style="flex-direction: column; align-items: flex-start; gap: 12px;">
                <h2 style="margin-bottom: 0;">📋 Daftar Pesanan Masuk</h2>

                <!-- FORM SEARCH & FILTER -->
                <form method="GET" action="" style="display: flex; gap: 8px; width: 100%; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Cari nama mahasiswa atau no. antrian..."
                        value="<?= htmlspecialchars($search) ?>"
                        style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;">

                    <select name="status" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Semua Status (Aktif)</option>
                        <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="diproses" <?= $filter_status == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="tertunda" <?= $filter_status == 'tertunda' ? 'selected' : '' ?>>Tertunda</option>
                        <option value="diambil" <?= $filter_status == 'diambil' ? 'selected' : '' ?>>Diambil (Selesai)</option>
                    </select>

                    <button type="submit" class="btn btn-primary">🔍 Cari / Filter</button>
                    <a href="dashboard.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Antrian</th>
                        <th>Waktu</th>
                        <th>Mahasiswa</th>
                        <th>Berkas</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nomor_antrian']) ?></strong></td>
                            <td><?= date('H:i', strtotime($row['tanggal_pesan'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>public/<?= str_replace('../../public/', '', $row['path_file'] ?? '') ?>" target="_blank" class="btn btn-sm btn-secondary">
                                    Lihat File
                                </a>
                            </td>
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
                            <td>
                                <a href="update-status.php?id=<?= $row['id_pesanan'] ?>" class="btn btn-sm btn-primary">Update Status</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pesanan)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Belum ada pesanan masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>