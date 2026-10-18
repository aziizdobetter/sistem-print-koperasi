<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_role('mahasiswa');

$antrian = isset($_GET['antrian']) ? htmlspecialchars($_GET['antrian']) : 'A000';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil — PrintKoperasi</title>
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
        <div class="card" style="max-width:600px; margin:40px auto; text-align:center;">
            <div style="font-size:64px; margin-bottom:16px;">✅</div>
            <h2 style="justify-content:center;">Pesanan Berhasil Dibuat!</h2>
            <p style="color:var(--gray); margin-bottom:24px;">
                Simpan nomor antrian ini untuk memantau status pesanan Anda.
            </p>

            <div style="background:var(--primary-light); border-radius:12px; padding:24px; margin-bottom:24px;">
                <p style="font-size:12px; color:var(--gray); margin-bottom:4px;">NOMOR ANTRIAN</p>
                <p style="font-size:48px; font-weight:800; color:var(--primary); letter-spacing:4px;"><?= $antrian ?></p>
                <p style="font-size:12px; color:var(--gray); margin-top:4px;">Status: <span class="badge badge-info">Menunggu</span></p>
            </div>

            <div style="display:flex; gap:8px; flex-direction:column;">
                <a href="dashboard.php" class="btn btn-primary btn-block">🏠 Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>

</html>