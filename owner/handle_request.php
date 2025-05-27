<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['request_id'], $_POST['action'], $_POST['sender_id'], $_POST['house_id'])) {

    $request_id = intval($_POST['request_id']);
    $house_id = intval($_POST['house_id']);
    $sender_id = intval($_POST['sender_id']); // Property Manager ID
    $owner_id = intval($_SESSION['user_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'declined';

    // Debugging - comment out or remove in production
    // var_dump("Session User ID:", $_SESSION['user_id']);
    // var_dump("POST sender_id:", $_POST['sender_id']);
    // exit;

    try {
        // 1. Verify current rental request status
        $stmt = $pdo->prepare("SELECT status FROM rental_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Rental request not found.");
        }

        if ($row['status'] !== 'forwarded') {
            throw new Exception("Request already responded to or in invalid state.");
        }

        // 2. Verify both users exist (owner and PM)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id IN (?, ?)");
        $stmt->execute([$owner_id, $sender_id]);
        $count = $stmt->fetchColumn();

        if ($count < 2) {
            throw new Exception("Invalid sender or receiver. User not found.");
        }

        // 3. Update rental_requests status
        $stmt = $pdo->prepare("UPDATE rental_requests SET status = ? WHERE id = ?");
        $stmt->execute([$action, $request_id]);

        // 4. Insert notification for the Property Manager
        $stmt = $pdo->prepare("
            INSERT INTO notifications (sender_id, receiver_id, message, type, status, house_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $owner_id,
            $sender_id,
            "Owner has $action the rental request.",
            'response',
            $action,
            $house_id
        ]);

        // 5. Redirect back to notifications page
        header("Location: notifications.php");
        exit;

    } catch (Exception $e) {
        // Display error message - consider logging errors instead in production
        echo "Error: " . htmlspecialchars($e->getMessage());
        exit;
    }
} else {
    // Redirect if accessed directly or POST data missing
    header("Location: notifications.php");
    exit;
}