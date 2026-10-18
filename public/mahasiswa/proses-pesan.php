<?php
require_once '../../config/config.php';
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_role('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengguna = $_SESSION['user_id'];
    $id_tarif = (int)$_POST['id_tarif'];
    $jumlah_lembar = (int)$_POST['jumlah_lembar'];

    // 1. VALIDASI INPUT
    if (empty($id_tarif) || $jumlah_lembar <= 0) {
        header("Location: pesan-cetak.php?error=Layanan dan jumlah lembar harus valid!");
        exit;
    }

    // 2. VALIDASI FILE
    $file = $_FILES['berkas'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: pesan-cetak.php?error=Gagal mengunggah berkas.");
        exit;
    }

    $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        header("Location: pesan-cetak.php?error=Format file tidak didukung! (Hanya PDF, Word, Image)");
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB
        header("Location: pesan-cetak.php?error=Ukuran file terlalu besar (Maks 5 MB).");
        exit;
    }

    // 3. AMBIL DATA TARIF & HITUNG BIAYA
    $stmtTarif = $pdo->prepare("SELECT * FROM tarif WHERE id_tarif = :id");
    $stmtTarif->execute([':id' => $id_tarif]);
    $tarif = $stmtTarif->fetch();

    if (!$tarif) {
        header("Location: pesan-cetak.php?error=Tarif tidak ditemukan.");
        exit;
    }

    $harga_satuan = $tarif['harga_per_lembar'];
    $total_biaya = $harga_satuan * $jumlah_lembar;

    // 4. PROSES UPLOAD FILE
    $nama_file_asli = $file['name'];
    $nama_file_baru = uniqid() . '.' . $ext; // Rename agar unik
    $path_file = '../../public/uploads/' . $nama_file_baru;

    if (!move_uploaded_file($file['tmp_name'], $path_file)) {
        header("Location: pesan-cetak.php?error=Gagal menyimpan berkas di server.");
        exit;
    }

    // 5. SIMPAN KE DATABASE (MENGGUNAKAN TRANSACTION)
    try {
        $pdo->beginTransaction();

        // A. Generate Nomor Antrian (Contoh: A001, A002, dst)
        // Cari nomor antrian terakhir hari ini
        $stmtAntrian = $pdo->query("SELECT nomor_antrian FROM pesanan ORDER BY id_pesanan DESC LIMIT 1");
        $last_antrian = $stmtAntrian->fetchColumn();

        if ($last_antrian) {
            $num = (int)substr($last_antrian, 1) + 1;
        } else {
            $num = 1;
        }
        $nomor_antrian = 'A' . str_pad($num, 3, '0', STR_PAD_LEFT);

        // B. Simpan ke tabel BERKAS
        $stmtBerkas = $pdo->prepare("INSERT INTO berkas (id_pengguna, nama_file, path_file, format_file, ukuran_file) VALUES (:id_user, :nama_file, :path, :format, :ukuran)");
        $stmtBerkas->execute([
            ':id_user' => $id_pengguna,
            ':nama_file' => $nama_file_asli,
            ':path' => $path_file,
            ':format' => $ext,
            ':ukuran' => round($file['size'] / 1024) // KB
        ]);
        $id_berkas = $pdo->lastInsertId();

        // C. Simpan ke tabel PESANAN
        $stmtPesanan = $pdo->prepare("INSERT INTO pesanan (nomor_antrian, id_pengguna, id_berkas, total_biaya, status_pesanan) VALUES (:antrian, :id_user, :id_berkas, :total, 'menunggu')");
        $stmtPesanan->execute([
            ':antrian' => $nomor_antrian,
            ':id_user' => $id_pengguna,
            ':id_berkas' => $id_berkas,
            ':total' => $total_biaya
        ]);
        $id_pesanan = $pdo->lastInsertId();

        // D. Simpan ke tabel DETAIL_PESANAN
        // Catatan: Kita butuh id_layanan dari tarif
        $stmtDetail = $pdo->prepare("INSERT INTO detail_pesanan (id_pesanan, id_layanan, id_tarif, jumlah_lembar, harga_satuan, subtotal) VALUES (:id_pesanan, :id_layanan, :id_tarif, :jumlah, :harga, :subtotal)");
        $stmtDetail->execute([
            ':id_pesanan' => $id_pesanan,
            ':id_layanan' => $tarif['id_layanan'],
            ':id_tarif' => $id_tarif,
            ':jumlah' => $jumlah_lembar,
            ':harga' => $harga_satuan,
            ':subtotal' => $total_biaya
        ]);

        // Commit transaksi
        $pdo->commit();

        // Redirect ke halaman sukses dengan membawa nomor antrian
        header("Location: sukses-pesan.php?antrian=" . $nomor_antrian);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        // Hapus file yang sudah terlanjur diupload jika database gagal
        if (file_exists($path_file)) unlink($path_file);

        header("Location: pesan-cetak.php?error=Gagal membuat pesanan: " . $e->getMessage());
        exit;
    }
}
