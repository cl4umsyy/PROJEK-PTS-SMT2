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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $image = $_FILES['image'] ?? null;

    if (!empty($title) && !empty($description) && $image['error'] === 0) {
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename to prevent overwriting
        $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $image_path = $upload_dir . $unique_filename;

        // Array of allowed file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file type
        if (!in_array($file_extension, $allowed_types)) {
            $message = '<div class="alert alert-danger">Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan!</div>';
        } else {
            // Attempt to upload file
            if (move_uploaded_file($image['tmp_name'], $image_path)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO fasilitas (judul, deskripsi, gambar) VALUES (:judul, :deskripsi, :gambar)");
                    $stmt->execute([
                        ':judul' => $title,
                        ':deskripsi' => $description,
                        ':gambar' => $unique_filename // Store only filename in database
                    ]);

                    // Redirect ke halaman fasilitas setelah berhasil
                    header("Location: fasilitas.php");
                    exit();
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Error database: ' . $e->getMessage() . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Gagal mengunggah gambar! Pastikan folder uploads memiliki permission yang benar.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-danger">Judul, Deskripsi, dan Gambar wajib diisi!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Fasilitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Tambah Fasilitas Baru</h4>
            </div>
            <div class="card-body">
                <?php if (isset($message)) echo $message; ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Fasilitas</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Masukkan judul fasilitas" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Masukkan deskripsi fasilitas" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Gambar</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format yang diperbolehkan: JPG, JPEG, PNG, GIF</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                        <a href="fasilitas.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
