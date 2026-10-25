<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('petugas');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail pesanan beserta nama mahasiswa dan detail layanannya
$sql = "SELECT p.*, u.nama_lengkap, b.nama_file, b.path_file 
        FROM pesanan p 
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna 
        JOIN berkas b ON p.id_berkas = b.id_berkas
        WHERE p.id_pesanan = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    header("Location: dashboard.php?error=Pesanan tidak ditemukan.");
    exit;
}

// Ambil detail pesanan (layanan apa saja yang dipesan)
$stmtDetail = $pdo->prepare("SELECT d.*, l.nama_layanan, t.ukuran_kertas, t.jenis_warna 
                             FROM detail_pesanan d 
                             JOIN layanan l ON d.id_layanan = l.id_layanan 
                             JOIN tarif t ON d.id_tarif = t.id_tarif
                             WHERE d.id_pesanan = :id");
$stmtDetail->execute([':id' => $id]);
$details = $stmtDetail->fetchAll();

// Validasi BR-13: Pesanan yang sudah diambil tidak bisa diubah
if ($pesanan['status_pesanan'] === 'diambil') {
    header("Location: dashboard.php?error=Pesanan sudah diambil dan tidak dapat diubah lagi.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Update Status — PrintKoperasi</title>
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
        <div class="breadcrumb">
            <a href="dashboard.php">🏠 Dashboard</a> &gt; 🔄 Update Status — <?= $pesanan['nomor_antrian'] ?>
        </div>

        <div class="grid-2">
            <!-- Kolom Kiri: Detail Pesanan -->
            <div class="card">
                <h2>Detail Pesanan</h2>
                <p><strong>No. Antrian:</strong> <?= $pesanan['nomor_antrian'] ?></p>
                <p><strong>Mahasiswa:</strong> <?= htmlspecialchars($pesanan['nama_lengkap']) ?></p>
                <p><strong>Waktu Pesan:</strong> <?= date('d M Y H:i', strtotime($pesanan['tanggal_pesan'])) ?></p>
                <p><strong>Berkas:</strong> <a href="../../<?= str_replace('../', '', $pesanan['path_file']) ?>" target="_blank" class="btn btn-sm btn-secondary">Unduh File</a></p>

                <hr style="border:none; border-top:1px dashed var(--border); margin:12px 0;">
                <p><strong>Rincian Layanan:</strong></p>
                <table>
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Spesifikasi</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nama_layanan']) ?></td>
                                <td><?= $d['ukuran_kertas'] ?> - <?= str_replace('_', '-', $d['jenis_warna']) ?></td>
                                <td><?= $d['jumlah_lembar'] ?></td>
                                <td>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>TOTAL</strong></td>
                            <td><strong>Rp <?= number_format($pesanan['total_biaya'], 0, ',', '.') ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Kolom Kanan: Form Update Status -->
            <div class="card">
                <h2>Ubah Status Pesanan</h2>
                <p>Status Saat Ini: <span class="badge badge-info"><?= ucfirst($pesanan['status_pesanan']) ?></span></p>

                <!-- SATU FORM UNTUK SEMUA AKSI -->
                <form action="proses-update.php" method="POST">
                    <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                    <input type="hidden" name="status_lama" value="<?= $pesanan['status_pesanan'] ?>">

                    <div class="form-group" style="margin-top:16px;">
                        <label>Pilih Status Baru</label>
                        <select name="status_baru" id="status_baru" required>
                            <?php if ($pesanan['status_pesanan'] === 'menunggu'): ?>
                                <option value="diproses">Diproses</option>
                            <?php elseif ($pesanan['status_pesanan'] === 'diproses'): ?>
                                <option value="selesai">Selesai</option>
                            <?php elseif ($pesanan['status_pesanan'] === 'selesai'): ?>
                                <option value="diambil">Diambil</option>
                            <?php elseif ($pesanan['status_pesanan'] === 'tertunda'): ?>
                                <option value="diproses">Lanjutkan ke Diproses</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Tampilkan checkbox pembayaran jika status akan diambil -->
                    <?php if ($pesanan['status_pesanan'] === 'selesai'): ?>
                        <div class="form-group" style="background:var(--primary-light); padding:12px; border-radius:8px; margin-bottom:12px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:0;">
                                <input type="checkbox" name="sudah_dibayar" value="1" style="width:auto;">
                                <span>Pembayaran tunai sudah diterima (Rp <?= number_format($pesanan['total_biaya'], 0, ',', '.') ?>)</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="keterangan" id="keterangan" rows="3" placeholder="Contoh: Tinta habis, menunggu isi ulang..."></textarea>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <!-- Tombol Utama: Simpan Perubahan Biasa -->
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

                        <!-- Tombol Tandai Tertunda (Hanya muncul jika status bukan tertunda) -->
                        <?php if ($pesanan['status_pesanan'] !== 'tertunda'): ?>
                            <button type="submit" name="aksi" value="tertunda" class="btn btn-secondary"
                                onclick="document.getElementById('status_baru').removeAttribute('required'); document.getElementById('status_baru').innerHTML = '<option value=\'tertunda\'>Tertunda</option>'; document.getElementById('keterangan').value = 'Stok kertas/tinta habis'; return confirm('Tandai pesanan sebagai Tertunda karena stok habis?');">
                                ⚠️ Tandai Tertunda (Stok Habis)
                            </button>
                        <?php endif; ?>

                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>