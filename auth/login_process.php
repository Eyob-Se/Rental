<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.php");
            exit;
        }

        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "Account is inactive. Please contact support.";
            header("Location: login.php");
            exit;
        }

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
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit;
    }
} else {
    die("Invalid request.");
}
?>