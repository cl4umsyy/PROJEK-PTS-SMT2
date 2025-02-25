<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda SD kaumandua Malang</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Slab:wght@400;700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
/* Header styling with green line (#4CCF66) */
.header {
  position: relative;
  background-color: white;
  color: #333;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 20px;
  border-top: 4px solid #103E35;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Secondary green line effect */


/* Logo and school info container */
.logo {
  display: flex;
  align-items: center;
  gap: 15px;
}

.logo img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 2px solid #103E35;
}

.school-info {
  display: flex;
  flex-direction: column;
}

.school-name {
  font-family: 'Roboto Slab', serif;
  font-weight: 700;
  font-size: 18px;
  color: #333;
  margin-bottom: 2px;
}

.motto {
  font-family: 'Montserrat', sans-serif;
  font-style: italic;
  font-size: 12px;
  color: #666;
}

/* Navigation styling */
.nav ul {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 5px;
}

.nav li {
  position: relative;
}

.nav a {
  display: block;
  padding: 10px 15px;
  color: #333;
  text-decoration: none;
  font-family: 'Roboto', sans-serif;
  font-weight: 500;
  font-size: 14px;
  transition: color 0.3s;
}

.nav a:hover {
  color: #103E35;
}

.nav a.active {
  color: #103E35;
  font-weight: 700;
}

.nav a.active::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 50%;
  transform: translateX(-50%);
  width: 80%;
  height: 3px;
  background-color: #103E35;
}

/* Keeping your footer styles but adjusting to match */
footer {
  background-color:rgb(16, 62, 53);
  color: white;
  padding: 40px 60px;
  font-family: 'Roboto Slab', sans-serif;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 30px;
}

.footer-info h2 {
  font-size: 20px;
  margin-bottom: 15px;
}

.footer-info ul {
  list-style: none;
  padding: 0;
}

.footer-info a {
  color: white;
  text-decoration: none;
  transition: opacity 0.3s;
}

.footer-info a:hover {
  opacity: 0.8;
}

.sosmed-links-footer {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.sosmed-links-footer img {
  width: 30px;
  height: 30px;
  transition: transform 0.3s;
}

.sosmed-links-footer img:hover {
  transform: scale(1.1);
}

.text-left {
  text-align: left;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    padding: 10px;
  }
  
  .nav ul {
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 10px;
  }
  
  .grid {
    grid-template-columns: 1fr;
  }
}
  </style>
</head>
<body>

<?php
// Data sekolah
$schoolLogo = "png_penting/logosd.jpg";
$schoolName = "SD Negeri 02 Kauman Malang";
$motto = "Satya Bhakti Pertiwi";

// Menentukan halaman yang aktif - default ke beranda
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// Definisikan menu items
$menuItems = [
    'index.php?page=beranda' => 'Beranda',
    'index.php?page=profil' => 'Profil',
    'index.php?page=prestasi' => 'Prestasi',
    'index.php?page=galeri' => 'Galeri',
    'index.php?page=fasilitas' => 'Fasilitas',
    'index.php?page=ekstrakurikuler' => 'Ekstrakurikuler',
    'index.php?page=masukan' => 'Masukan',
];
?>

<header class="header">
    <div class="logo">
        <img src="<?php echo $schoolLogo; ?>" alt="School Logo">
        <div class="school-info">
            <div class="school-name"><?php echo $schoolName; ?></div>
            <div class="motto"><?php echo $motto; ?></div>
        </div>
    </div>

    <nav class="nav">
        <ul>
            <?php foreach ($menuItems as $link => $name): ?>
                <li><a href="<?php echo $link; ?>" <?php echo ($page === strtolower(str_replace('index.php?page=', '', $link))) ? 'class="active"' : ''; ?>><?php echo $name; ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>
      
<?php
switch ($page) {
    case 'beranda':
        include 'pages/beranda.php';
        break;
    case 'profil':
        include 'pages/profil.php';
        break;
    case 'prestasi':
        include 'pages/prestasi.php';
        break;
    case 'galeri':
        include 'pages/galeri.php';
        break;
    case 'fasilitas':
        include 'pages/fasilitas.php';
        break;
    case 'ekstrakurikuler':
        include 'pages/ekstrakurikuler.php';
        break;
    case 'masukan':
        include 'pages/masukan.php';
        break;
    default:
        include 'pages/beranda.php'; // Fallback to beranda if page not found
        break;
}
?>

<footer>
    <div class="footer-info">
        <div class="grid">
            <div>
                <h3>Location</h3>
                <img src="png_penting/peta.png" alt="Map showing location in Malang, Indonesia" class="w-full" width="300" height="250">
            </div>
            <div>
                <h3 class="text-left">Contact us</h3>
                <div class="text-left">
                    <p>+62 (0341) 354254</p>
                    <p>kaumandua@rocketmail.com</p>
                    <h3>Follow us</h3>
                    <div class="sosmed-links-footer">
                        <a href="https://www.instagram.com/sdnkauman2malang/?hl=id" target="_blank">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo">
                        </a>
                        <a href="https://www.facebook.com/p/SDN-Kauman-2-Malang-100071041394695/?locale=id_ID" target="_blank">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo">
                        </a>
                        <a href="https://www.youtube.com/@sdnkauman2519" target="_blank">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/youtube-play.png" alt="YouTube Logo">
                        </a>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-left">Navigation</h3>
                <ul class="text-left">
                    <?php foreach ($menuItems as $link => $name): ?>
                        <li><a href="<?php echo $link; ?>"><?php echo $name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div><br>
        <div class="footer-location">
            <h3>SD Kauman 2 Malang</h3>
            <p>Jalan Kawi No. 24D, Kelurahan Kauman, Kecamatan Klojen, Kota Malang, Jawa Timur, dengan kode pos 65119.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">  
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>