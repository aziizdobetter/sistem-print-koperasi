<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_role('petugas');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas</title>
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
            <h2>Selamat Datang di Dashboard Petugas</h2>
            <p>Ini adalah halaman yang hanya bisa diakses oleh role <strong>Petugas</strong>.</p>
        </div>
    </div>
</body>

</html>