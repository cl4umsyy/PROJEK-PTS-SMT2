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

// Koneksi Database
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8",
        $db_config['user'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Proses Tambah Prestasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $gambar = '';

    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = basename($_FILES["gambar"]["name"]);
            } else {
                echo "<script>alert('Gagal mengunggah gambar.');</script>";
            }
        } else {
            echo "<script>alert('Format gambar tidak didukung.');</script>";
        }
    }

    if (!empty($judul) && !empty($gambar) && !empty($deskripsi)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO prestasi (judul, gambar, deskripsi) VALUES (?, ?, ?)");
            $stmt->execute([$judul, $gambar, $deskripsi]);
            echo "<script>alert('Prestasi berhasil ditambahkan!'); window.location='tambah_prestasi.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Gagal menyimpan data: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Semua kolom harus diisi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Prestasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4">Tambah Prestasi</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" name="judul" id="judul" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="prestasi.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
