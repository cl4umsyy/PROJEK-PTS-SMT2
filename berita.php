<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database Configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'sdn_kauman02',
    'user' => 'root',
    'password' => ''
];

// Image Storage Configurations
$image_config = [
    'internal_dir' => 'png_penting/',
    'external_dir' => '/var/www/uploads/sdn_kauman02/news/'
];

// Database Connection
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

// Image Handling Function
function getImagePath($filename, $config) {
    $internal_path = $config['internal_dir'] . $filename;
    if (file_exists($internal_path)) {
        return $internal_path;
    }
    $external_path = $config['external_dir'] . $filename;
    if (file_exists($external_path)) {
        return $external_path;
    }
    return false;
}

// Handle Delete AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        try {
            // First get the image filename
            $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);

            // Then delete the record
            $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
            $result = $stmt->execute([$_POST['id']]);

            if ($result) {
                // Delete the image file if it exists
                if ($news && !empty($news['image'])) {
                    $image_path = getImagePath($news['image'], $image_config);
                    if ($image_path && file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                echo "success";
            } else {
                echo "error";
            }
        } catch (PDOException $e) {
            echo "error";
        }
        exit;
    }
}

// Fetch News
try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    $news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - SDN Kauman 02</title>
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
        .custom-green {
            background-color: #103E35 !important;
            border-color: #103E35 !important;
            color: white !important;
        }
        .custom-green:hover {
            background-color: #103E35 !important;
            border-color: #103E35 !important;
        }
        .table thead th {
            background-color: #103E35 !important;
            color: white !important;
        }
        .table img {
            width: 120px;
            height: 80px;
            object-fit: cover;
        }
        .news-description {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            <h4>Daftar Berita</h4>
            <div class="user-info d-flex align-items-center">
            <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <img src="userr.png" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
            </div>
        </div>

        <div class="mb-4">
            <a href="tambah_berita.php" class="btn custom-green">
                <i class="fas fa-plus me-2"></i>Tambah Berita Baru
            </a>
        </div>

        <!-- Alert Messages -->
        <div id="alertMessage" class="alert" style="display: none;"></div>

        <!-- Table Section -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($news_list)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada berita yang dipublikasikan</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($news_list as $index => $news): ?>
                    <tr id="news-row-<?php echo $news['id']; ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($news['title']); ?></td>
                        <td>
                            <img src="image_handler.php?image=<?php echo urlencode($news['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?>"
                                 class="img-thumbnail">
                        </td>
                        <td><?php echo date('d M Y', strtotime($news['created_at'])); ?></td>
                        <td class="news-description">
                            <?php echo htmlspecialchars(strip_tags($news['content'])); ?>
                        </td>
                        <td>
                            <a href="edit_berita.php?id=<?php echo $news['id']; ?>" 
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger btn-sm delete-news" 
                                    data-id="<?php echo $news['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show Alert Function
            function showAlert(message, type) {
                const alertDiv = $("#alertMessage");
                alertDiv.removeClass().addClass(`alert alert-${type}`).html(message).show();
                setTimeout(() => alertDiv.fadeOut(), 3000);
            }

            // Handle Delete
            $(".delete-news").on("click", function() {
                if (confirm("Apakah Anda yakin ingin menghapus berita ini?")) {
                    const id = $(this).data('id');
                    $.ajax({
                        url: "berita.php",
                        type: "POST",
                        data: { 
                            action: 'delete',
                            id: id 
                        },
                        success: function(response) {
                            if (response === "success") {
                                $(`#news-row-${id}`).remove();
                                showAlert("Berita berhasil dihapus!", "success");
                                
                                // Reload if no news left
                                if ($("tbody tr").length === 0) {
                                    location.reload();
                                }
                            } else {
                                showAlert("Gagal menghapus berita.", "danger");
                            }
                        },
                        error: function() {
                            showAlert("Terjadi kesalahan saat menghapus berita.", "danger");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>