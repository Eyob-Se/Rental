<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT t.*, h.title FROM transactions t JOIN houses h ON t.house_id = h.id WHERE t.tenant_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Payments</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
<div class="prop_con">
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="../tenant/dashboard.php">Dashboard</a></li>
            <li><a href="payments.php" class="active">My Payments</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <section>
            <h3>My Payments</h3>
            <table class="user-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>House</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['title']) ?></td>
                            <td>$<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= htmlspecialchars(ucfirst($payment['status'])) ?></td>
                            <td><?= date("F j, Y", strtotime($payment['created_at'] ?? 'now')) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br />
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </section>
    </div>
</div>
</body>
</html>
