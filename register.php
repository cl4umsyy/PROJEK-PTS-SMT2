<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - SD Negeri Kauman 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .auth-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .logo-img {
            width: 120px;
            height: 120px;
            display: block;
            margin: 0 auto 1.5rem auto;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
            padding: 3px;
            background-color: white;
        }
        .btn-custom {
            background-color: #73EC8B;
            border-color: #73EC8B;
            color: white;
        }
        .btn-custom:hover {
            background-color: #5ACE70;
            border-color: #5ACE70;
        }
        .form-control:focus {
            border-color: #73EC8B;
            box-shadow: 0 0 0 0.2rem rgba(115, 236, 139, 0.25);
        }
        .form-control::placeholder {
            color: #73EC8B;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <img src="logosd.jpg" alt="Logo SD Negeri Kauman 02" class="logo-img">

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['message']);
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <form action="handle_register.php" method="POST" autocomplete="off">
            <h2 class="text-center mb-4">Register</h2>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="new-password">
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn btn-custom w-100">Register</button>
        </form>
    </div>
</body>
</html>