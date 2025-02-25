<?php
// Koneksi ke database
$host = "localhost";
$user = "root"; // Sesuaikan dengan username database Anda
$pass = ""; // Sesuaikan dengan password database Anda
$db   = "sdn_kauman02"; // Sesuaikan dengan nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM gallery ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            margin: auto;
            text-align: center;
            padding: 20px;
        }

        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .gallery-card {
            width: 250px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }

        .gallery-card:hover {
            transform: scale(1.05);
        }

        .gallery-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .gallery-title {
            font-size: 16px;
            margin: 10px 0;
            font-weight: bold;
        }

        .add-button {
            display: inline-block;
            padding: 10px 15px;
            margin-bottom: 20px;
            background-color: green;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .delete-button {
            display: inline-block;
            padding: 8px 12px;
            background-color: red;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .delete-button:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <nav>
        <!-- Navbar tetap, tidak diubah -->
    </nav>

    <div class="container">
        <h2>Galeri</h2>
        <a href="tambah_galeri.php" class="add-button">Tambah Gambar</a>
        <div class="gallery-container">
            <?php while ($row = mysqli_fetch_assoc($query)) : ?>
                <div class="gallery-card">
                    <img src="<?= $row['image'] ?>" alt="image" class="gallery-image">
                    <h3 class="gallery-title"><?= $row['title'] ?></h3>
                    <a href="delete_galeri.php?id=<?= $row['id'] ?>" class="delete-button" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
