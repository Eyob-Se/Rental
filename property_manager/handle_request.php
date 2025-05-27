<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pm_id = $_SESSION['user_id'];
    $tenant_id = $_POST['tenant_id'] ?? null;
    $house_id = $_POST['house_id'] ?? null;
    $action = $_POST['action'] ?? null;

    try {
        if (!is_numeric($house_id) || !is_numeric($tenant_id)) {
            die("Invalid input data.");
        }

        if ($action === 'send') {
            // Forward request: set status = 'forwarded' and pm_id = current property manager id
            $stmt = $pdo->prepare("
                UPDATE rental_requests 
                SET status = 'forwarded', pm_id = ? 
                WHERE tenant_id = ? AND house_id = ?
            ");
            $stmt->execute([$pm_id, $tenant_id, $house_id]);

            echo "✅ Request successfully forwarded to the owner.<br>";
            echo "<a href='review_request.php'>⬅️ Go back</a>";
            exit;
        }

        if ($action === 'approved' || $action === 'declined') {
            // Notify tenant after owner response (update status only)
            $stmt = $pdo->prepare("
                UPDATE rental_requests 
                SET status = ? 
                WHERE tenant_id = ? AND house_id = ?
            ");
            $stmt->execute([$action, $tenant_id, $house_id]);

            echo "✅ Tenant has been updated about the {$action} decision.<br>";
            echo "<a href='notifications.php'>⬅️ Go back</a>";
            exit;
        }

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>