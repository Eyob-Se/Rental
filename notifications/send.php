<?php
session_start();
require_once '../config/db.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sender_id = $_SESSION['user_id'];

// POST data: receiver_id, message
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['receiver_id']) || empty($data['message'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'receiver_id and message are required']);
    exit;
}

$receiver_id = intval($data['receiver_id']);
$message = trim($data['message']);

// Validate message length (optional)
if (strlen($message) > 500) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Message too long']);
    exit;
}

try {
    $sql = "INSERT INTO notifications (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sender_id' => $sender_id,
        ':receiver_id' => $receiver_id,
        ':message' => $message
    ]);
    echo json_encode(['success' => 'Notification sent']);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
