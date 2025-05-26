<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Store user info in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header("Location: ../admin/manage_pm.php");
                break;
            case 'tenant':
                header("Location: ../tenant/dashboard.php");
                break;
            case 'owner':
                header("Location: ../owner/dashboard.php");
                break;
            case 'property_manager':
                header("Location: ../property_manager/dashboard.php");
                break;
            case 'government':
                header("Location: ../government/view_reports.php");
                break;
            default:
                $_SESSION['error'] = "Unknown user role.";
                header("Location: login.php");
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password, or account inactive.";
        header("Location: login.php");
        exit;
    }
} else {
    die("Invalid request.");
}
