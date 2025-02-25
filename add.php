<?php
include 'fasilitas.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    // Upload Gambar
    $gambar = $_FILES['gambar']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($gambar);
    move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);

    // Insert ke Database
    $sql = "INSERT INTO fasilitas (judul, gambar, deskripsi) VALUES ('$judul', '$gambar', '$deskripsi')";
    if ($conn->query($sql) === TRUE) {
        echo "Fasilitas berhasil ditambahkan!";
        header("Location: index.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Fasilitas</title>
</head>
<body>
    <h2>Tambah Fasilitas</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Judul:</label>
        <input type="text" name="judul" required><br><br>

        <label>Gambar:</label>
        <input type="file" name="gambar" required><br><br>

        <label>Deskripsi:</label>
        <textarea name="deskripsi" required></textarea><br><br>

        <button type="submit">Tambah</button>
    </form>
    <br>
    <a href="index.php">Kembali</a>
</body>
</html>
