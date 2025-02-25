<?php
session_start();
require_once 'koneksi.php';

// Function to check if user is superadmin
function isSuperAdmin($username, $password) {
    global $koneksi;
    $query = "SELECT * FROM admin WHERE username = ? AND password = ? AND role = 'superadmin'";
    $stmt = $koneksi->prepare($query);
    $hashedPassword = md5($password);
    $stmt->bind_param("ss", $username, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to list all admins (only for superadmin)
function getAllAdmins() {
    global $koneksi;
    $query = "SELECT id, username, role FROM admin WHERE role = 'admin'";
    $result = $koneksi->query($query);
    return $result;
}

// Function to add new admin (only for superadmin) - FIXED to check for existing username
function addAdmin($username, $password) {
    global $koneksi;
    
    // First check if username already exists
    $check_query = "SELECT * FROM admin WHERE username = ?";
    $check_stmt = $koneksi->prepare($check_query);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Username already exists
        return false;
    }
    
    // Username doesn't exist, proceed with insert
    $hashedPassword = md5($password);
    $role = 'admin';
    
    $query = "INSERT INTO admin (username, password, role) VALUES (?, ?, ?)";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("sss", $username, $hashedPassword, $role);
    return $stmt->execute();
}

// Function to edit admin (only for superadmin)
function editAdmin($id, $username, $password) {
    global $koneksi;
    
    // Check if new username already exists (but ignore current admin)
    $check_query = "SELECT * FROM admin WHERE username = ? AND id != ?";
    $check_stmt = $koneksi->prepare($check_query);
    $check_stmt->bind_param("si", $username, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Username already exists
        return false;
    }
    
    $hashedPassword = md5($password);
    
    $query = "UPDATE admin SET username = ?, password = ? WHERE id = ? AND role = 'admin'";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ssi", $username, $hashedPassword, $id);
    return $stmt->execute();
}

// Function to delete admin (only for superadmin)
function deleteAdmin($id) {
    global $koneksi;
    $query = "DELETE FROM admin WHERE id = ? AND role = 'admin'";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Check if user is logged in as superadmin
if (!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isSuperAdmin($_SESSION['username'], $_SESSION['password'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (addAdmin($_POST['username'], $_POST['password'])) {
                    $message = "Admin berhasil ditambahkan";
                } else {
                    $error = "Gagal menambahkan admin. Username sudah digunakan.";
                }
                break;
                
            case 'edit':
                if (editAdmin($_POST['id'], $_POST['username'], $_POST['password'])) {
                    $message = "Admin berhasil diupdate";
                } else {
                    $error = "Gagal mengupdate admin. Username sudah digunakan.";
                }
                break;
                
            case 'delete':
                if (deleteAdmin($_POST['id'])) {
                    $message = "Admin berhasil dihapus";
                } else {
                    $error = "Gagal menghapus admin";
                }
                break;
        }
    }
}

// Get list of admins
$admins = getAllAdmins();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Admin</title>
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
        }
        .sidebar .nav-link.active {
            background-color: #e9ecef;
            font-weight: bold;
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
                        <a href="dashboard.php" class="nav-link">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                        <a href="kelola_admin.php" class="nav-link active">
                            <i class="fas fa-users-cog"></i> Kelola Admin
                        </a>
                        <a href="beranda.php" class="nav-link">
                            <i class="fas fa-comments"></i> Masukan
                        </a>
                        <a href="berita.php" class="nav-link">
                            <i class="fas fa-newspaper"></i> Berita
                        </a>
                        <a href="galeri.php" class="nav-link">
                            <i class="fas fa-images"></i> Galeri
                        </a>
                        <a href="fasilitas.php" class="nav-link">
                            <i class="fas fa-building"></i> Fasilitas
                        </a>
                        <a href="prestasi.php" class="nav-link">
                            <i class="fas fa-trophy"></i> Prestasi
                        </a>
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 py-3">
                <div class="container">
                    <h2 class="mb-4">Kelola Admin</h2>
                    
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah Admin -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Tambah Admin Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label class="form-label">Username:</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password:</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Admin
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Daftar Admin -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Daftar Admin</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $admins->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Yakin ingin menghapus admin ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Admin -->
                                        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Admin</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Username Baru:</label>
                                                                <input type="text" name="username" class="form-control" 
                                                                    value="<?php echo htmlspecialchars($row['username']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Password Baru:</label>
                                                                <input type="password" name="password" class="form-control" required>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-save"></i> Simpan Perubahan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>