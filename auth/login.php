<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, arahkan ke index
    header("Location: " . "../public/index.php");
    exit;
}
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PrintKoperasi</title>
    <!-- Pastikan path CSS benar -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo-area">
                <div class="logo-icon">🖨️</div>
                <h1>PrintKoperasi</h1>
                <p>Sistem Print/Fotokopi Koperasi UIN Malang</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div style="background:#f8d7da; color:#dc3545; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="process_login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username / NIM</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan NIM atau username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>
        </div>
    </div>
</body>

</html>