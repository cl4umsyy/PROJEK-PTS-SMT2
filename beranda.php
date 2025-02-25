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

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

if (isset($_GET['delete_id'])) {
    $feedback_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = :id");
        $stmt->bindParam(':id', $feedback_id);
        if ($stmt->execute()) {
            echo 'success';
            exit;
        } else {
            echo 'error';
            exit;
        }
    } catch (PDOException $e) {
        echo 'error';
        exit;
    }
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM feedback");
    $total_feedback = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT * FROM feedback 
              WHERE DATE(created_at) BETWEEN :start_date AND :end_date";

    if ($status_filter != 'all') {
        $query .= " AND status = :status";
    }
    $query .= " ORDER BY created_at ASC LIMIT 10";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);

    if ($status_filter != 'all') {
        $stmt->bindParam(':status', $status_filter);
    }

    $stmt->execute();
    $feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Dashboard - SDN Kauman 02</title>
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
        .table-custom-green {
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
            <h4>Masukan</h4>
            <div class="user-info d-flex align-items-center">
            <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <img src="userr.png" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3" method="GET">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn custom-green">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header table-custom-green">
                <h5 class="card-title mb-0">Laporan Masukan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-custom-green">
                            <tr>
                                <th>Nomor</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal</th>
                                <th>Pesan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback_list as $feedback): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                                <td><?php echo date('d M Y', strtotime($feedback['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($feedback['message']); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-danger delete-feedback" data-id="<?php echo $feedback['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".delete-feedback").on("click", function(e) {
                e.preventDefault();
                if (confirm("Apakah Anda yakin ingin menghapus masukan ini?")) {
                    var feedbackId = $(this).data('id');
                    var row = $(this).closest("tr");
                    $.ajax({
                        url: "",
                        type: 'GET',
                        data: { delete_id: feedbackId },
                        success: function(response) {
                            if (response === 'success') {
                                row.fadeOut();
                            } else {
                                alert("Gagal menghapus masukan.");
                            }
                        },
                        error: function() {
                            alert("Terjadi kesalahan saat menghapus masukan.");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>