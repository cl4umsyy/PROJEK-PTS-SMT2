<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Konfigurasi Database
$db_config = [
    'host' => 'localhost',
    'dbname' => 'sdn_kauman02',
    'user' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8",
        $db_config['user'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Get image filename before deleting record
        $stmt = $pdo->prepare("SELECT gambar FROM fasilitas WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();

        // Delete record from database
        $stmt = $pdo->prepare("DELETE FROM fasilitas WHERE id = ?");
        $stmt->execute([$id]);

        // Delete image file if exists
        if ($image && file_exists('uploads/' . $image)) {
            unlink('uploads/' . $image);
        }

        echo "success";
    } catch (PDOException $e) {
        echo "error";
    }
} else {
    echo "error";
}