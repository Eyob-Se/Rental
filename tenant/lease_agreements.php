<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT la.*, h.title FROM lease_agreements la JOIN houses h ON la.house_id = h.id WHERE la.tenant_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$leases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Lease Agreements</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
<div class="prop_con">
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="../tenant/dashboard.php">Dashboard</a></li>
            <li><a href="lease_agreements.php" class="active">My Lease Agreements</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <section>
            <h3>My Lease Agreements</h3>
            <table class="user-table" id="leaseAgreementsTable">
                <thead>
                    <tr>
                        <th>House</th>
                        <th>Signed Date</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leases)): ?>
                        <?php foreach ($leases as $lease): ?>
                        <tr>
                            <td><?= htmlspecialchars($lease['title']) ?></td>
                            <td><?= date("F j, Y", strtotime($lease['created_at'])) ?></td>
                            <td>
                                <?php if (!empty($lease['file_path'])): ?>
                                    <a class="btn" href="../leases/<?= htmlspecialchars($lease['file_path']) ?>" target="_blank">View Lease</a>
                                <?php else: ?>
                                    <em>No file</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;">No lease agreements found.</td></tr>
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
