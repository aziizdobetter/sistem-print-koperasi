<?php
require_once '../../../config/config.php';
require_once '../../../auth/check_auth.php';
require_once '../../../config/database.php';
require_role('admin');

// Ambil semua data layanan
$stmt = $pdo->query("SELECT * FROM layanan ORDER BY id_layanan DESC");
$layanan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Layanan — PrintKoperasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
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
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>public/admin/dashboard.php">🏠 Dashboard</a> &gt; 📦 Kelola Layanan
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Daftar Layanan</h2>
                <a href="create.php" class="btn btn-primary">+ Tambah Layanan</a>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div style="background:#d4edda; color:#28a745; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($layanan as $row): ?>
                        <tr>
                            <td><?= $row['id_layanan'] ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_layanan']) ?></strong></td>
                            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                            <td><?= htmlspecialchars($row['satuan']) ?></td>
                            <td>
                                <?php if ($row['status_aktif']): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $row['id_layanan'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="delete.php?id=<?= $row['id_layanan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus layanan ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($layanan)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Belum ada data layanan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>