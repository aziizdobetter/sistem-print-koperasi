<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('mahasiswa');

// Ambil data layanan aktif
$stmtLayanan = $pdo->query("SELECT * FROM layanan WHERE status_aktif = 1");
$layanan = $stmtLayanan->fetchAll();

// Ambil data tarif (kita join dengan layanan)
$stmtTarif = $pdo->query("SELECT t.*, l.nama_layanan FROM tarif t JOIN layanan l ON t.id_layanan = l.id_layanan WHERE l.status_aktif = 1");
$tarif = $stmtTarif->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pesan Cetak — PrintKoperasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
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
        <div class="breadcrumb">
            <a href="dashboard.php">🏠 Dashboard</a> &gt; 📋 Pesan Cetak
        </div>

        <div class="card" style="max-width: 700px; margin: 0 auto;">
            <h2>Form Pesan Cetak</h2>

            <?php if (isset($_GET['error'])): ?>
                <div style="background:#f8d7da; color:#dc3545; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="proses-pesan.php" method="POST" enctype="multipart/form-data">

                <!-- 1. Upload Berkas -->
                <div class="form-group">
                    <label>1. Upload Berkas (PDF/Word/Image) <span style="color:red;">*</span></label>
                    <input type="file" name="berkas" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required style="border: 1px solid #dee2e6; padding: 10px; border-radius: 8px; width: 100%;">
                    <small style="color:var(--gray);">Maksimal 5 MB.</small>
                </div>

                <!-- 2. Spesifikasi -->
                <label style="font-weight:600; display:block; margin-bottom:8px;">2. Spesifikasi Cetakan</label>
                <div class="form-row">
                    <div class="form-group">
                        <label>Layanan & Tarif <span style="color:red;">*</span></label>
                        <select name="id_tarif" id="id_tarif" required onchange="hitungBiaya()">
                            <option value="">-- Pilih Layanan --</option>
                            <?php foreach ($tarif as $t): ?>
                                <option value="<?= $t['id_tarif'] ?>" data-harga="<?= $t['harga_per_lembar'] ?>">
                                    <?= htmlspecialchars($t['nama_layanan']) ?> - <?= $t['ukuran_kertas'] ?> - <?= str_replace('_', '-', $t['jenis_warna']) ?> (Rp <?= number_format($t['harga_per_lembar'], 0, ',', '.') ?>/lembar)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Lembar <span style="color:red;">*</span></label>
                        <input type="number" name="jumlah_lembar" id="jumlah_lembar" min="1" value="1" required oninput="hitungBiaya()">
                    </div>
                </div>

                <!-- 3. Estimasi Biaya -->
                <div class="form-group">
                    <label>3. Estimasi Biaya</label>
                    <div style="background:var(--primary-light); padding:16px; border-radius:8px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span>Harga per lembar</span>
                            <strong id="label_harga">Rp 0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span>Jumlah lembar</span>
                            <strong id="label_jumlah">1</strong>
                        </div>
                        <hr style="border:none; border-top:1px dashed var(--border); margin:8px 0;">
                        <div style="display:flex; justify-content:space-between;">
                            <span style="font-weight:600;">TOTAL</span>
                            <strong id="label_total" style="color:var(--primary); font-size:18px;">Rp 0</strong>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:8px; margin-top:16px;">
                    <button type="submit" class="btn btn-primary">Konfirmasi Pesanan</button>
                    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function hitungBiaya() {
            const select = document.getElementById('id_tarif');
            const jumlah = document.getElementById('jumlah_lembar').value;
            const harga = select.options[select.selectedIndex].getAttribute('data-harga') || 0;

            const total = harga * jumlah;

            document.getElementById('label_harga').innerText = 'Rp ' + parseInt(harga).toLocaleString('id-ID');
            document.getElementById('label_jumlah').innerText = jumlah;
            document.getElementById('label_total').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>

</html>