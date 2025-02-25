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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data fasilitas yang akan diedit
try {
    $stmt = $pdo->prepare("SELECT * FROM fasilitas WHERE id = ?");
    $stmt->execute([$id]);
    $fasilitas = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$fasilitas) {
        header("Location: fasilitas.php");
        exit();
    }
} catch (PDOException $e) {
    die("Query gagal: " . $e->getMessage());
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $gambar_lama = $fasilitas['gambar'];
    
    try {
        if (!empty($_FILES['gambar']['name'])) {
            // Ada upload gambar baru
            $gambar = $_FILES['gambar'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($gambar['type'], $allowed_types)) {
                throw new Exception('Tipe file tidak diizinkan. Gunakan JPG, PNG, atau GIF.');
            }
            
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($gambar['size'] > $max_size) {
                throw new Exception('Ukuran file terlalu besar. Maksimal 5MB.');
            }
            
            $filename = uniqid() . '_' . $gambar['name'];
            $upload_path = 'uploads/' . $filename;
            
            if (move_uploaded_file($gambar['tmp_name'], $upload_path)) {
                // Hapus gambar lama jika ada
                if ($gambar_lama && file_exists('uploads/' . $gambar_lama)) {
                    unlink('uploads/' . $gambar_lama);
                }
                
                // Update database dengan gambar baru
                $stmt = $pdo->prepare("UPDATE fasilitas SET judul = ?, deskripsi = ?, gambar = ? WHERE id = ?");
                $stmt->execute([$judul, $deskripsi, $filename, $id]);
            } else {
                throw new Exception('Gagal mengupload file.');
            }
        } else {
            // Tidak ada upload gambar baru, update tanpa gambar
            $stmt = $pdo->prepare("UPDATE fasilitas SET judul = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$judul, $deskripsi, $id]);
        }
        
        // Setelah berhasil update, redirect ke fasilitas.php
        header("Location: fasilitas.php");
        exit();
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Fasilitas - SDN Kauman 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Edit Fasilitas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul</label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo htmlspecialchars($fasilitas['judul']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                          required><?php echo htmlspecialchars($fasilitas['deskripsi']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar</label>
                                <?php if ($fasilitas['gambar']): ?>
                                    <div class="mb-2">
                                        <img src="uploads/<?php echo htmlspecialchars($fasilitas['gambar']); ?>" 
                                             alt="Preview" class="preview-image">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="fasilitas.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>