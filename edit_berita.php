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
    header("Location: berita.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$berita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$berita) {
    header("Location: berita.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    $updateFields = ["title = ?, content = ?"];
    $params = [$title, $content, $id];
    
    // Handle image upload if a new image is provided
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $fileName = time() . '_' . $file['name'];
        $targetPath = 'png_penting/' . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old image
            if ($berita['image'] && file_exists('png_penting/' . $berita['image'])) {
                unlink('png_penting/' . $berita['image']);
            }
            $updateFields[] = "image = ?";
            $params = [$title, $content, $fileName, $id];
        }
    }
    
    try {
        $sql = "UPDATE news SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        header("Location: berita.php");
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
    <title>Edit Berita - SDN Kauman 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Berita</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($berita['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Konten</label>
                                <textarea class="form-control" id="content" name="content" 
                                          rows="6" required><?php echo htmlspecialchars($berita['content']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Gambar Baru (Opsional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <?php if ($berita['image']): ?>
                                    <div class="mt-2">
                                        <small>Gambar Saat Ini:</small><br>
                                        <img src="image_handler.php?image=<?php echo urlencode($berita['image']); ?>" 
                                             alt="Current Image" style="max-width: 200px; margin-top: 10px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="berita.php" class="btn btn-secondary">Kembali</a>
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