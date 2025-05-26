<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$tenant_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$tenant_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching notifications: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenant Notifications</title>
    <style>
        body { font-family: Arial; }
        .notification { background: #f5f5f5; padding: 12px; margin-bottom: 10px; border-radius: 6px; }
        .date { font-size: 0.85em; color: #666; }
    </style>
</head>
<body>
    <h2>Your Notifications</h2>

    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <?php foreach ($notifications as $note): ?>
            <div class="notification">
                <p><?= htmlspecialchars($note['message']) ?></p>
                <p class="date"><?= date('F j, Y, g:i a', strtotime($note['created_at'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
