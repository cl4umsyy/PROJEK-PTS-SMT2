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

// Get counts and recent items
try {
    // Count total items
    $prestasi_count = $pdo->query("SELECT COUNT(*) FROM prestasi")->fetchColumn();
    $fasilitas_count = $pdo->query("SELECT COUNT(*) FROM fasilitas")->fetchColumn();
    $galeri_count = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();

    // Get recent prestasi
    $recent_prestasi = $pdo->query("SELECT * FROM prestasi ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent fasilitas
    $recent_fasilitas = $pdo->query("SELECT * FROM fasilitas ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent gallery items
    $recent_gallery = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    
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
        .stat-card {
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .recent-item {
            transition: transform 0.2s ease;
        }
        .recent-item:hover {
            transform: scale(1.02);
        }
        .gallery-preview {
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        /* Modified Activity Timeline with scrolling */
        .activity-timeline-container {
            height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #6c757d #f8f9fa;
        }
        
        .activity-timeline-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .activity-timeline-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .activity-timeline-container::-webkit-scrollbar-thumb {
            background-color: #6c757d;
            border-radius: 10px;
        }
        
        .activity-timeline {
            position: relative;
            padding-top: 1rem;
        }
        
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 25px;
            top: 0;
            height: 100%;
            width: 2px;
            background: #dee2e6;
        }
        
        .timeline-item-enhanced {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            border-left: 2px solid #e9ecef;
            margin-left: 2rem;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .timeline-item-enhanced:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .timeline-icon {
            position: absolute;
            left: -2.5rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .timeline-content {
            flex: 1;
            padding-left: 0.5rem;
        }
        
        /* Gallery Styles */
        .gallery-card {
            position: relative;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .gallery-image-wrapper {
            position: relative;
            padding-top: 75%;
            overflow: hidden;
        }
        
        .gallery-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
        }
        
        .gallery-title {
            font-size: 0.9rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .gallery-date {
            font-size: 0.75rem;
            opacity: 0.8;
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
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Dashboard</h4>
                    <div class="user-info d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <img src="userr.png" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Prestasi</h6>
                                        <h2 class="mb-0"><?php echo $prestasi_count; ?></h2>
                                    </div>
                                    <i class="fas fa-trophy fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Fasilitas</h6>
                                        <h2 class="mb-0"><?php echo $fasilitas_count; ?></h2>
                                    </div>
                                    <i class="fas fa-building fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-danger text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Galeri</h6>
                                        <h2 class="mb-0"><?php echo $galeri_count; ?></h2>
                                    </div>
                                    <i class="fas fa-images fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Recent Activities Section with Scrolling -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                                <span class="badge bg-primary rounded-pill"><?php echo count($recent_prestasi) + count($recent_fasilitas); ?> activities</span>
                            </div>
                            <div class="card-body p-0">
                                <!-- Added scrollable container -->
                                <div class="activity-timeline-container">
                                    <div class="activity-timeline p-3">
                                        <?php foreach ($recent_prestasi as $prestasi): ?>
                                        <div class="timeline-item-enhanced">
                                            <div class="timeline-icon bg-primary">
                                                <i class="fas fa-trophy text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-1 text-primary">Prestasi Baru</h6>
                                                    <small class="text-muted timestamp">Hari ini</small>
                                                </div>
                                                <h6 class="activity-title"><?php echo htmlspecialchars($prestasi['judul']); ?></h6>
                                                <p class="mb-0 text-muted"><?php echo substr($prestasi['deskripsi'], 0, 100); ?>...</p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <?php foreach ($recent_fasilitas as $fasilitas): ?>
                                        <div class="timeline-item-enhanced">
                                            <div class="timeline-icon bg-success">
                                                <i class="fas fa-building text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-1 text-success">Fasilitas Baru</h6>
                                                    <small class="text-muted timestamp">Hari ini</small>
                                                </div>
                                                <h6 class="activity-title"><?php echo htmlspecialchars($fasilitas['judul']); ?></h6>
                                                <p class="mb-0 text-muted"><?php echo substr($fasilitas['deskripsi'], 0, 100); ?>...</p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Recent Gallery -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Galeri Terbaru</h5>
                                <span class="badge bg-danger rounded-pill"><?php echo count($recent_gallery); ?> photos</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <?php foreach ($recent_gallery as $gallery): ?>
                                    <div class="col-md-6">
                                        <div class="gallery-card">
                                            <div class="gallery-image-wrapper">
                                                <img src="<?php echo htmlspecialchars($gallery['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($gallery['title']); ?>"
                                                     class="gallery-image">
                                                <div class="gallery-overlay">
                                                    <h6 class="gallery-title"><?php echo htmlspecialchars($gallery['title']); ?></h6>
                                                    <span class="gallery-date">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        <?php echo date('d M Y', strtotime($gallery['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>