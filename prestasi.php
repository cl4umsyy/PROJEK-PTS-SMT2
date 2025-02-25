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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM prestasi WHERE id = ?");
            $result = $stmt->execute([$_POST['id']]);
            echo $result ? "success" : "error";
        } catch (PDOException $e) {
            echo "error";
        }
        exit;
    }
}

// Ambil data prestasi
try {
    $stmt = $pdo->query("SELECT * FROM prestasi ORDER BY id DESC");
    $prestasi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query gagal: " . $e->getMessage());
}
function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestasi - SDN Kauman 02</title>
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
            width: 80px;
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

            <!-- Konten Utama -->
            <div class="col-md-9 col-lg-10 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Daftar Prestasi</h4>
                    <div class="user-info d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <img src="userr.png" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                    </div>
                </div>

                <div class="mb-4">
                    <a href="tambah_prestasi.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Prestasi Baru
                    </a>
                </div>

                <!-- Alert Messages -->
                <div id="alertMessage" class="alert" style="display: none;"></div>

                <!-- Tabel Prestasi -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Gambar</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($prestasi_list)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada prestasi yang ditambahkan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($prestasi_list as $index => $prestasi): ?>
                                    <tr id="prestasi-row-<?php echo $prestasi['id']; ?>">
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($prestasi['judul']); ?></td>
                                        <td>
                                            <img src="uploads/<?php echo htmlspecialchars($prestasi['gambar']); ?>" 
                                                 alt="Gambar Prestasi">
                                        </td>
                                        <td><?php echo htmlspecialchars($prestasi['deskripsi']); ?></td>
                                        <td>
                                            <a href="edit_prestasi.php?id=<?php echo $prestasi['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm delete-prestasi" 
                                                    data-id="<?php echo $prestasi['id']; ?>">
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
            $(".delete-prestasi").on("click", function() {
                if (confirm("Apakah Anda yakin ingin menghapus prestasi ini?")) {
                    const id = $(this).data('id');
                    $.ajax({
                        url: "prestasi.php",
                        type: "POST",
                        data: { 
                            action: 'delete',
                            id: id 
                        },
                        success: function(response) {
                            if (response === "success") {
                                $(`#prestasi-row-${id}`).remove();
                                showAlert("Prestasi berhasil dihapus!", "success");
                            } else {
                                showAlert("Gagal menghapus prestasi.", "danger");
                            }
                        },
                        error: function() {
                            showAlert("Terjadi kesalahan saat menghapus prestasi.", "danger");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>