<?php
// Konfigurasi database
$host = 'localhost';      // Host database
$username = 'root';       // Username database
$password = '';          // Password database (kosong untuk XAMPP default)
$database = 'sdn_kauman02'; // Nama database

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Set charset ke utf8
$koneksi->set_charset("utf8");