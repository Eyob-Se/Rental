<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    $pendingHouses = $pdo->query("SELECT COUNT(*) FROM houses WHERE status = 'pending'")->fetchColumn();
    $pendingRequests = $pdo->query("SELECT COUNT(*) FROM rental_requests WHERE status = 'pending'")->fetchColumn();
    $reports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PM Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../assets/style1.css" />
    <link rel="stylesheet" href="../assets/fonts/all.css" />
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
        <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
        <h2>Welcome, Property Manager</h2>
        <p>Dashboard overview</p>

        <div class="card">
            <h3>Pending Houses: <?= $pendingHouses ?></h3><br>

        </div>

        <div class="card">
            <h3>Pending Tenant Requests: <?= $pendingRequests ?></h3><br>

        </div>

        <div class="card">
            <h3>Reports Sent: <?= $reports ?></h3><br>
            
        </div>
    </div>

</div>
<script src="../../assets/main.js"></script>
</body>
</html>
