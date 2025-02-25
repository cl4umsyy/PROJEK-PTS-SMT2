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

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $title = $_POST['title'];
        $uploadDir = 'uploads/gallery/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . time() . '_' . $fileName;
        $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes) && move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, image_path) VALUES (:title, :image_path)");
            $stmt->execute(['title' => $title, 'image_path' => $targetPath]);
            header("Location: galeri.php");
            exit();
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $title = $_POST['title'];
        
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = 'uploads/gallery/';
            $fileName = basename($_FILES['image']['name']);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif']) && 
                move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                
                $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
                $stmt->execute([$id]);
                $oldImage = $stmt->fetchColumn();
                
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }

                $stmt = $pdo->prepare("UPDATE gallery SET title = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$title, $targetPath, $id]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE gallery SET title = ? WHERE id = ?");
            $stmt->execute([$title, $id]);
        }
        header("Location: galeri.php");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $imagePath = $stmt->fetchColumn();
    
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
    
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: galeri.php");
    exit();
}

// Fetch gallery items
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - SDN Kauman 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #FBFBFB;
            color: #333;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: #000;
            background-color: rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background-color: rgba(0, 0, 0, 0.2);
            font-weight: bold;
        }
        .sidebar .nav-link.text-danger {
            color: #dc3545 !important;
        }
        .table img {
            width: 120px;
            height: 80px;
            object-fit: cover;
        }
        .btn-primary {
            background-color: #103E35;
            border-color: #103E35;
        }
        .btn-primary:hover {
            background-color: #0c2e28;
            border-color: #0c2e28;
        }
        .table-primary {
            background-color: #103E35 !important;
            color: white;
        }
        .table-primary th {
            background-color: #103E35 !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar py-3">
                <div class="d-flex flex-column">
                    <div class="text-center mb-4">
                        <img src="logosd-removebg-preview.png" alt="Logo" class="img-fluid mb-2" style="max-width: 100px;">
                        <h5>SDN Kauman 02</h5>
                    </div>

                    <div class="nav flex-column">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                        <?php if (isSuperAdmin()): ?>
                        <a href="kelola_admin.php" class="nav-link">
                            <i class="fas fa-users-cog"></i> Kelola Admin
                        </a>
                        <?php endif; ?>
                        <a href="beranda.php" class="nav-link">
                            <i class="fas fa-comments me-2"></i> Masukan
                        </a>
                        <a href="berita.php" class="nav-link">
                            <i class="fas fa-newspaper me-2"></i> Berita
                        </a>
                        <a href="galeri.php" class="nav-link">
                            <i class="fas fa-images me-2"></i> Galeri
                        </a>
                        <a href="fasilitas.php" class="nav-link">
                            <i class="fas fa-building me-2"></i> Fasilitas
                        </a>
                        <a href="prestasi.php" class="nav-link">
                            <i class="fas fa-trophy me-2"></i> Prestasi
                        </a>
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Galeri</h4>
                    <div class="user-info d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <img src="userr.png" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                    </div>
                </div>

                <div class="mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
                        <i class="fas fa-plus me-2"></i>Tambah Foto
                    </button>
                </div>

                <!-- Table Section -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Gambar</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($gallery_items)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada foto dalam galeri</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($gallery_items as $index => $item): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 class="img-thumbnail">
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#editGalleryModal"
                                                    data-id="<?php echo $item['id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                                    data-image="<?php echo htmlspecialchars($item['image_path']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=<?php echo $item['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Gallery Modal -->
    <div class="modal fade" id="addGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Foto Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Gallery Modal -->
    <div class="modal fade" id="editGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" class="form-control" name="title" id="edit-title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Baru (opsional)</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Saat Ini</label>
                            <img id="edit-image-preview" src="" alt="Gambar Saat Ini" style="max-width: 100%; height: auto;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#editGalleryModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var title = button.data('title');
            var image = button.data('image');
            
            var modal = $(this);
            modal.find('#edit-id').val(id);
            modal.find('#edit-title').val(title);
            modal.find('#edit-image-preview').attr('src', image);
        });
    </script>
</body>
</html>