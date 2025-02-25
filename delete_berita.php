<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
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

        // Get image filename before deletion
        $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $news = $stmt->fetch();

        if ($news) {
            // Delete the image file
            $image_path = "png_penting/" . $news['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }

            // Delete the database record
            $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }

        header("Location: berita.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

header("Location: berita.php");
exit();