<?php
session_start();
require_once 'koneksi.php';

// Handle Login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password harus diisi";
        header("Location: login.php");
        exit();
    }

    try {
        // Prepare statement
        $stmt = $koneksi->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Verify credentials
        if ($user && md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['password'] = $password; // Diperlukan untuk fungsi isSuperAdmin
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Username atau password salah";
            header("Location: login.php");
            exit();
        }
    } catch(Exception $e) {
        $_SESSION['error'] = "Login gagal. Silakan coba lagi.";
        header("Location: login.php");
        exit();
    }
}