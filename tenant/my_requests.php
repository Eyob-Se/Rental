<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT rr.*, h.title FROM rental_requests rr JOIN houses h ON rr.house_id = h.id WHERE rr.tenant_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Rent Requests</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
<div class="prop_con">
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
                <li><a href="dashboard.php">Home</a></li>
            <li><a href="view_houses.php">View Available Houses</a></li>
            <li><a href="my_requests.php">My Requests</a></li>
            <li><a href="my_payments.php">My Payments</a></li>
            <li><a href="lease_agreements.php">Lease Agreements</a></li>
            </ul>
            <button><a href="../auth/logout.php">Logout</a></button>
    </div>

    <div class="container">
            <h3>My Rent Requests</h3>
            <table class="user-table" id="requestsTable">
                <thead>
                    <tr>
                        <th>House</th>
                        <th>Status</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['title']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($req['status'])) ?></td>
                            <td><?= date("F j, Y", strtotime($req['created_at'] ?? 'now')) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;">No rental requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br />
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
