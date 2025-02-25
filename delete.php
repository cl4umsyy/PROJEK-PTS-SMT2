<?php
include 'fasilitas.php';

$id = $_GET['id'];

// Hapus data dari database
$sql = "DELETE FROM fasilitas WHERE id=$id";
if ($conn->query($sql) === TRUE) {
    echo "Fasilitas berhasil dihapus!";
    header("Location: index.php");
} else {
    echo "Error: " . $conn->error;
}
?>
