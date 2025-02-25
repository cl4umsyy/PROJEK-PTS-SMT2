<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
    die("Koneksi database gagal: " . $e->getMessage());
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: prestasi.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM prestasi WHERE id = ?");
$stmt->execute([$id]);
$prestasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestasi) {
    header("Location: prestasi.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    
    $updateFields = ["judul = ?, deskripsi = ?"];
    $params = [$judul, $deskripsi, $id];
    
    // Handle image upload if a new image is provided
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        $fileName = time() . '_' . $file['name'];
        $targetPath = 'uploads/' . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old image
            if ($prestasi['gambar'] && file_exists('uploads/' . $prestasi['gambar'])) {
                unlink('uploads/' . $prestasi['gambar']);
            }
            $updateFields[] = "gambar = ?";
            $params = [$judul, $deskripsi, $fileName, $id];
        }
    }
    
    try {
        $sql = "UPDATE prestasi SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        header("Location: prestasi.php");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal memperbarui data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prestasi - SDN Kauman 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Prestasi</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul</label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo htmlspecialchars($prestasi['judul']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                          rows="4" required><?php echo htmlspecialchars($prestasi['deskripsi']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar Baru (Opsional)</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <?php if ($prestasi['gambar']): ?>
                                    <div class="mt-2">
                                        <small>Gambar Saat Ini:</small><br>
                                        <img src="uploads/<?php echo htmlspecialchars($prestasi['gambar']); ?>" 
                                             alt="Current Image" style="max-width: 200px; margin-top: 10px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="prestasi.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>