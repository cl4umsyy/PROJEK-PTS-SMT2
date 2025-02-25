<?php
$db_host = 'localhost';
$db_user = 'root';  // Change this to your MySQL username
$db_pass = '';      // Change this to your MySQL password
$db_name = 'sdn_kauman02';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}