<?php
// public/test-koneksi.php
require_once '../config/database.php';

echo "<h2>Pengujian Koneksi Database</h2>";

try {
    // 1. Uji Koneksi
    echo "<p>✅ <b>Database dapat diakses.</b> Terhubung ke database: <code>$dbname</code></p>";

    // 2. Uji Baca Data (READ)
    echo "<h3>Uji Baca Data (READ)</h3>";
    $stmt = $pdo->query("SELECT id_layanan, nama_layanan, satuan FROM layanan LIMIT 3");
    $layanan = $stmt->fetchAll();

    if ($layanan) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Nama Layanan</th><th>Satuan</th></tr>";
        foreach ($layanan as $row) {
            echo "<tr><td>{$row['id_layanan']}</td><td>{$row['nama_layanan']}</td><td>{$row['satuan']}</td></tr>";
        }
        echo "</table>";
        echo "<p>✅ <b>Data dapat dibaca.</b></p>";
    } else {
        echo "<p>⚠️ Tabel layanan kosong.</p>";
    }

    // 3. Uji Simpan Data (CREATE) - Kita tambahkan layanan baru
    echo "<h3>Uji Simpan Data (CREATE)</h3>";
    $sqlInsert = "INSERT INTO layanan (nama_layanan, deskripsi, satuan, status_aktif) 
                  VALUES (:nama, :deskripsi, :satuan, 1)";
    $stmt = $pdo->prepare($sqlInsert);
    $stmt->execute([
        ':nama' => 'Scan Dokumen',
        ':deskripsi' => 'Layanan scan dokumen ke PDF',
        ':satuan' => 'lembar'
    ]);
    $lastId = $pdo->lastInsertId();
    echo "<p>✅ <b>Data dapat disimpan.</b> Menambahkan layanan ID: <b>{$lastId}</b> (Scan Dokumen)</p>";

    // 4. Uji Update Data (UPDATE) - Kita ubah deskripsi layanan yang baru saja dibuat
    echo "<h3>Uji Update Data (UPDATE)</h3>";
    $sqlUpdate = "UPDATE layanan SET deskripsi = :deskripsi WHERE id_layanan = :id";
    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute([
        ':deskripsi' => 'Layanan scan dokumen (Updated)',
        ':id' => $lastId
    ]);
    echo "<p>✅ <b>Data dapat diperbarui.</b> Update deskripsi layanan ID: <b>{$lastId}</b>.</p>";

    // 5. Uji Hapus Data (DELETE) - Kita hapus data uji coba tadi agar database bersih
    echo "<h3>Uji Hapus Data (DELETE)</h3>";
    $sqlDelete = "DELETE FROM layanan WHERE id_layanan = :id";
    $stmt = $pdo->prepare($sqlDelete);
    $stmt->execute([':id' => $lastId]);
    echo "<p>✅ <b>Data dapat dihapus.</b> Menghapus layanan ID: <b>{$lastId}</b>.</p>";
} catch (PDOException $e) {
    echo "<p>❌ <b>Terjadi kesalahan:</b> " . $e->getMessage() . "</p>";
}
