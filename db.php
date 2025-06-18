<?php
// db.php - koneksi database MySQL
$host = "localhost";
$user = "root";
$password = "";
$database = "pa_simalungun";

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set character set untuk mendukung UTF-8
$conn->set_charset("utf8mb4");
?>

