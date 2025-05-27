<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/fonts/all.css">
</head>
<body>
    <div class="login-page">
        <form action="login_process.php" method="POST" class="login-form">
            <h2>Login</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?= htmlspecialchars($_SESSION['success']) ?></p>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="form-container">
                <label for="email"><i class="fas fa-envelope"></i> <b>Email</b></label>
                <input type="email" name="email" placeholder="Enter your email" autocomplete="off" required>

                <label for="password"><i class="fas fa-lock"></i> <b>Password</b></label>
                <input type="password" name="password" placeholder="Enter your password" autocomplete="new-password" required>

                <button type="submit">Login</button>
                <button type="button" class="cancelbtn" onclick="window.location.href='../index.php'">Cancel</button>
            </div>

            <div class="form-links">
                <a href="register.php">Don't have an account? Register</a>
            </div>
        </form>
    </div>
</body>
</html>
