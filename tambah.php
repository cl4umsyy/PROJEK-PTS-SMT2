<?php
$koneksi = new mysqli("localhost", "root", "", "sdn_kauman02");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $gambar = $_FILES['gambar']['name'];
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    $file_extension = pathinfo($gambar, PATHINFO_EXTENSION);
    
    if (in_array(strtolower($file_extension), $allowed_extensions)) {
        $target_dir_inside = "uploads/";
        $target_dir_outside = "../uploads/";
        
        // Pastikan folder uploads ada
        if (!file_exists($target_dir_inside)) {
            mkdir($target_dir_inside, 0777, true);
        }
        if (!file_exists($target_dir_outside)) {
            mkdir($target_dir_outside, 0777, true);
        }
        
        $target_inside = $target_dir_inside . basename($gambar);
        $target_outside = $target_dir_outside . basename($gambar);
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_inside)) {
            $sql = "INSERT INTO galeri (judul, gambar) VALUES ('$judul', '$target_inside')";
            if ($koneksi->query($sql) === TRUE) {
                header("galeri.php");
                exit;
            } else {
                echo "Error: " . $sql . "<br>" . $koneksi->error;
            }
        } elseif (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_outside)) {
            $sql = "INSERT INTO galeri (judul, gambar) VALUES ('$judul', '$target_outside')";
            if ($koneksi->query($sql) === TRUE) {
                header("Location: galeri.php");
                exit;
            } else {
                echo "Error: " . $sql . "<br>" . $koneksi->error;
            }
        } else {
            echo "Gagal mengupload gambar.";
        }
    } else {
        echo "Format file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
    }
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Galeri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            width: 50%;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 50px;
        }
        input, button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tambah Galeri</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Judul:</label>
            <input type="text" name="judul" required><br>
            <label>Gambar:</label>
            <input type="file" name="gambar" required><br>
            <button type="submit">Tambah</button>
        </form>
    </div>
</body>
</html>
