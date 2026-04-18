<?php
// config.php
// Koneksi MySQL (mysqli) + inisialisasi database & tabel
// Sesuaikan credential di bawah bila perlu

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';        // default XAMPP biasanya kosong
$DB_NAME = 'nusantara_attire';

// buat koneksi awal (tanpa db) untuk membuat database jika belum ada
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// buat database jika belum ada
$sqlCreateDb = "CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sqlCreateDb)) {
    die('Gagal membuat database: ' . $conn->error);
}

// pilih database
$conn->select_db($DB_NAME);

// set utf8
$conn->set_charset('utf8mb4');

// ----- buat tabel jika belum ada -----
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS baju (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    jenis VARCHAR(100) NOT NULL,
    ukuran VARCHAR(50) NOT NULL,
    harga INT NOT NULL,
    status ENUM('ready','keluar') NOT NULL DEFAULT 'ready'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS penyewa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    telp VARCHAR(50) DEFAULT NULL,
    alamat TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS sewa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewa INT NOT NULL,
    id_baju INT NOT NULL,
    tgl_sewa DATE NOT NULL,
    tgl_kembali DATE NOT NULL,
    total_harga INT NOT NULL,
    status_bayar ENUM('belum','lunas') NOT NULL DEFAULT 'belum',
    FOREIGN KEY (id_penyewa) REFERENCES penyewa(id) ON DELETE CASCADE,
    FOREIGN KEY (id_baju) REFERENCES baju(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ----- user default (admin/admin) jika belum ada -----
$res = $conn->query("SELECT COUNT(*) AS c FROM users");
$row = $res->fetch_assoc();
if ((int)$row['c'] === 0) {
    // simpan password plaintext sesuai file lama; idealnya gunakan password_hash()
    $conn->query("INSERT INTO users (username,password) VALUES ('admin','admin')");
}

// sekarang $conn siap dipakai di file lain
?>
