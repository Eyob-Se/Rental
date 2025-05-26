<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT rr.*, 
           h.title AS house_title, h.owner_id, h.price,
           u.name AS tenant_name, u.id AS tenant_id,
           rr.created_at
    FROM rental_requests rr
    JOIN houses h ON rr.house_id = h.id
    JOIN users u ON rr.tenant_id = u.id
    WHERE rr.status = 'pending'
");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Rental Requests</title>
    <link rel="stylesheet" href="../assets/style1.css">
    <link rel="stylesheet" href="../assets/fonts/all.css">
</head>
<body>
<div class="prop_con">

    <!-- Navigation Bar -->
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="review_request.php">Requests</a></li>
            <li><a href="review_house.php">Approvals</a></li>
            <li><a href="lease-agreements.php">Agreements</a></li>
                <li><a href="generate_report.php">Report</a></li>
        </ul>
        <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
        <div class="top-bar">
            <input type="text" id="requestFilter" placeholder="🔍 Filter rental requests" />
        </div>

        <section>
            <h3>Tenant Rental Requests</h3>
            <table class="user-table" id="rentalRequestTable">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>House</th>
                        <th>Request Date</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests): ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['tenant_name']) ?></td>
                                <td><?= htmlspecialchars($req['house_title']) ?> ($<?= number_format($req['price'], 2) ?>)</td>
                                <td><?= date('Y-m-d', strtotime($req['created_at'])) ?></td>
                                <td><span class="status inactive">Waiting</span></td>
                                <td><span class="status inactive">Pending</span></td>
                                <td>
<form action="handle_request.php" method="POST">
    <input type="hidden" name="tenant_id" value="<?= $req['tenant_id'] ?>">
    <input type="hidden" name="house_id" value="<?= $req['house_id'] ?>">
    <input type="hidden" name="action" value="send">
    <button type="submit" class="btn">Forward to Owner</button>
</form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No pending rental requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<script src="../../assets/main.js"></script>
</body>
</html>
