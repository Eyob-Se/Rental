<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';

try {
    if ($unread_only) {
        $sql = "SELECT n.*, u.name AS sender_name FROM notifications n JOIN users u ON n.sender_id = u.id WHERE receiver_id = :user_id AND is_read = 0 ORDER BY created_at DESC";
    } else {
        $sql = "SELECT n.*, u.name AS sender_name FROM notifications n JOIN users u ON n.sender_id = u.id WHERE receiver_id = :user_id ORDER BY created_at DESC";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notifications);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
