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
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Define upload directory outside web root (adjust path as needed)
    $upload_base_dir = '/var/www/uploads/sdn_kauman02/news/';
    
    // Ensure the directory exists and is writable
    if (!is_dir($upload_base_dir)) {
        mkdir($upload_base_dir, 0755, true);
    }
    
    if (empty($_FILES['image']['name'])) {
        $error = "Please select an image";
    } else {
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed";
        } else {
            // Generate a unique, cryptographically secure filename
            $new_filename = bin2hex(random_bytes(16)) . '.' . $file_extension;
            
            // Full path for storing the file
            $target_file = $upload_base_dir . $new_filename;
            
            // Additional security: check file type using mime type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
            finfo_close($finfo);
            
            $allowed_mime_types = [
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => ['png'],
                'image/gif' => ['gif']
            ];
            
            if (!isset($allowed_mime_types[$mime_type]) || 
                !in_array($file_extension, $allowed_mime_types[$mime_type])) {
                $error = "Invalid file type";
            } else {
                // Check file size (e.g., limit to 5MB)
                if ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
                    $error = "File is too large. Maximum size is 5MB";
                } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    try {
                        // Store only the filename in the database
                        $stmt = $pdo->prepare("INSERT INTO news (title, content, image) VALUES (:title, :content, :image)");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':image', $new_filename);
                        
                        if ($stmt->execute()) {
                            header("Location: berita.php");
                            exit();
                        }
                    } catch (PDOException $e) {
                        $error = "Error adding news: " . $e->getMessage();
                        // Remove the file if database insertion fails
                        if (file_exists($target_file)) {
                            unlink($target_file);
                        }
                    }
                } else {
                    $error = "Error uploading file";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita - SDN Kauman 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Berita Baru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Konten</label>
                                <textarea class="form-control" name="content" rows="6" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Gambar</label>
                                <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/gif" required>
                                <small class="text-muted">Format yang diperbolehkan: JPG, JPEG, PNG, GIF (maks 5MB)</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="berita.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan Berita</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (document.querySelector('.preview-image')) {
                        document.querySelector('.preview-image').remove();
                    }
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.marginTop = '10px';
                    img.className = 'preview-image';
                    this.parentElement.appendChild(img);
                }.bind(this);
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>