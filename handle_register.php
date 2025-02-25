<?php
session_start();

// Database configuration
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

    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username already taken";
        header("Location: register.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    
    // Get the new user's ID
    $user_id = $pdo->lastInsertId();
    
    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    
    // Redirect to dashboard.php
    header("Location: dashboard.php");
    exit();

} catch(PDOException $e) {
    $_SESSION['error'] = "Registration failed: " . $e->getMessage();
    header("Location: register.php");
    exit();
}
?>