<?php
// config/database.php

// Konfigurasi Database (Sesuaikan dengan settingan MySQL lokal Anda)
$host = 'localhost';
$port = '3306';
$dbname = 'db_print_koperasi'; // Database dari Modul 5
$username = 'root';            // Default XAMPP/Laragon
$password = '';                // Default XAMPP kosong, Laragon biasanya kosong

// DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

// Opsi PDO untuk keamanan dan error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception jika error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil fetch berupa array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan prepared statements asli dari MySQL
];

try {
    // Membuat koneksi PDO
    $pdo = new PDO($dsn, $username, $password, $options);

    // Jika berhasil (untuk keperluan testing awal)
    // echo "Koneksi ke database berhasil!";

} catch (PDOException $e) {
    // Jika gagal, tampilkan pesan error (jangan tampilkan detail password di production)
    die("Koneksi database gagal: " . $e->getMessage());
}
