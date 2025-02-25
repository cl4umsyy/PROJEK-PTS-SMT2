<?php
include 'fasilitas.php';

$id = $_GET['id'];
$sql = "SELECT * FROM fasilitas WHERE id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    
    // Periksa apakah pengguna mengunggah gambar baru
    if ($_FILES['gambar']['name']) {
        $gambar = $_FILES['gambar']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($gambar);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);

        // Update dengan gambar baru
        $sql = "UPDATE fasilitas SET judul='$judul', gambar='$gambar', deskripsi='$deskripsi' WHERE id=$id";
    } else {
        // Update tanpa mengubah gambar
        $sql = "UPDATE fasilitas SET judul='$judul', deskripsi='$deskripsi' WHERE id=$id";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Fasilitas berhasil diperbarui!";
        header("Location: index.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Fasilitas</title>
</head>
<body>
    <h2>Edit Fasilitas</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Judul:</label>
        <input type="text" name="judul" value="<?= $row['judul']; ?>" required><br><br>

        <label>Gambar:</label>
        <input type="file" name="gambar"><br><br>
        <img src="uploads/<?= $row['gambar']; ?>" width="100"><br><br>

        <label>Deskripsi:</label>
        <textarea name="deskripsi" required><?= $row['deskripsi']; ?></textarea><br><br>

        <button type="submit">Update</button>
    </form>
    <br>
    <a href="index.php">Kembali</a>
</body>
</html>
