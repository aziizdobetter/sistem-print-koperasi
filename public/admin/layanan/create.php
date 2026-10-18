<?php
require_once '../../../config/config.php';
require_once '../../../auth/check_auth.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Layanan — PrintKoperasi</title>
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
            <a href="index.php">📦 Kelola Layanan</a> &gt; Tambah
        </div>

        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Tambah Layanan Baru</h2>

            <?php if (isset($_GET['error'])): ?>
                <div style="background:#f8d7da; color:#dc3545; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="store.php" method="POST">
                <div class="form-group">
                    <label>Nama Layanan <span style="color:red;">*</span></label>
                    <input type="text" name="nama_layanan" placeholder="Contoh: Print, Fotokopi" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat layanan"></textarea>
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color:red;">*</span></label>
                    <input type="text" name="satuan" placeholder="Contoh: lembar, buku" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_aktif">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <div style="display:flex; gap:8px; margin-top: 16px;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>