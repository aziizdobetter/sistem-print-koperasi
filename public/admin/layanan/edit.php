<?php
require_once '../../../config/config.php';
require_once '../../../auth/check_auth.php';
require_once '../../../config/database.php';
require_role('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM layanan WHERE id_layanan = :id");
$stmt->execute([':id' => $id]);
$layanan = $stmt->fetch();

if (!$layanan) {
    header("Location: index.php?error=Data tidak ditemukan.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Layanan — PrintKoperasi</title>
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
            <a href="<?= BASE_URL ?>public/admin/dashboard.php">🏠 Dashboard</a> &gt;
            <a href="index.php">📦 Kelola Layanan</a> &gt; Edit
        </div>

        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Edit Layanan</h2>

            <?php if (isset($_GET['error'])): ?>
                <div style="background:#f8d7da; color:#dc3545; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="update.php" method="POST">
                <input type="hidden" name="id_layanan" value="<?= $layanan['id_layanan'] ?>">

                <div class="form-group">
                    <label>Nama Layanan <span style="color:red;">*</span></label>
                    <input type="text" name="nama_layanan" value="<?= htmlspecialchars($layanan['nama_layanan']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3"><?= htmlspecialchars($layanan['deskripsi']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color:red;">*</span></label>
                    <input type="text" name="satuan" value="<?= htmlspecialchars($layanan['satuan']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_aktif">
                        <option value="1" <?= $layanan['status_aktif'] == 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= $layanan['status_aktif'] == 0 ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <div style="display:flex; gap:8px; margin-top: 16px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>