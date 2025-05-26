<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications where the current user is the receiver
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE receiver_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Notifications</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
<div class="prop_con">
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="review_request.php">Requests</a></li>
            <li><a href="review_house.php">Approvals</a></li>
            <li><a href="lease-agreements.php">Agreements</a></li>
            <li><a href="generate_report.php">Report</a></li>
        </ul>
        <button class="btn" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
        <button class="btn" onclick="window.location.href='dashboard.php'">Back to dashboard</button>
        <h2>Your Notifications</h2>
        <ul class="notif-list">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $note): ?>
                    <li class="<?= $note['is_read'] ? 'read' : 'unread' ?>">
                        <?= htmlspecialchars($note['message']) ?>
                        <br>
                        <small><em><?= date("M d, Y h:i A", strtotime($note['created_at'])) ?></em></small>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No notifications found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</body>
</html>
