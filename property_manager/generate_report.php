<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

$reportMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $pm_id = $_SESSION['user_id'];

    if ($subject && $description) {
        $stmt = $pdo->prepare("INSERT INTO reports_for_admin (property_manager_id, subject, description) VALUES (?, ?, ?)");
        $stmt->execute([$pm_id, $subject, $description]);
        $reportMessage = " Report submitted successfully.";
    } else {
        $reportMessage = " All fields are required.";
    }
}


// Fetch tenants info
$tenantStmt = $pdo->prepare("
    SELECT
        tp.user_id,
        u.name,
        tp.phone,
        COUNT(DISTINCT h.id) AS houses_rented,
        COUNT(DISTINCT la.id) AS lease_agreements_signed
    FROM tenant_profiles tp
    JOIN users u ON u.id = tp.user_id
    LEFT JOIN lease_agreements la ON la.tenant_id = tp.user_id AND la.signed_by_tenant = 1
    LEFT JOIN houses h ON h.id = la.house_id AND h.is_rented = 1
    GROUP BY tp.user_id, u.name, tp.phone
    ORDER BY u.name
");
$tenantStmt->execute();
$tenants = $tenantStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch owners info
$ownerStmt = $pdo->prepare("
    SELECT
        op.user_id,
        u.name,
        op.phone,
        op.bank,
        op.account,
        COUNT(h.id) AS houses_posted,
        SUM(CASE WHEN h.status = 'approved' THEN 1 ELSE 0 END) AS houses_approved,
        SUM(CASE WHEN h.status = 'rented' OR h.is_rented = 1 THEN 1 ELSE 0 END) AS houses_rented
    FROM owner_profiles op
    JOIN users u ON u.id = op.user_id
    LEFT JOIN houses h ON h.owner_id = op.user_id
    GROUP BY op.user_id, u.name, op.phone, op.bank, op.account
    ORDER BY u.name
");
$ownerStmt->execute();
$owners = $ownerStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Generate Report</title>
    <link rel="stylesheet" href="../assets/style1.css" />
    <style>
    table {
        color: #f4f4f4;
        border-collapse: collapse;
        margin-bottom: 30px;
        width: 100%;
    }

    th,
    td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        color: black;
        background-color: #f4f4f4;
    }

    h3 {
        margin-top: 40px;
    }

    form label {
        color: #f4f4f4;
    }
    </style>
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
                <li><a href="generate_report.php">Reports</a></li>
            </ul>
            <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
        </div>

        <div class="container">
            <h2>Generate Report</h2>

            <?php if ($reportMessage): ?>
            <p style="color: <?= str_starts_with($reportMessage, '✅') ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($reportMessage) ?>
            </p>
            <?php endif; ?>

            <h3>Tenants</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Houses Rented</th>
                        <th>Lease Agreements Signed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td><?= htmlspecialchars($tenant['user_id']) ?></td>
                        <td><?= htmlspecialchars($tenant['name']) ?></td>
                        <td><?= htmlspecialchars($tenant['phone']) ?></td>
                        <td><?= htmlspecialchars($tenant['houses_rented']) ?></td>
                        <td><?= htmlspecialchars($tenant['lease_agreements_signed']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Owners</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Bank</th>
                        <th>Bank Account</th>
                        <th>Houses Posted</th>
                        <th>Houses Approved</th>
                        <th>Houses Rented</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($owners as $owner): ?>
                    <tr>
                        <td><?= htmlspecialchars($owner['user_id']) ?></td>
                        <td><?= htmlspecialchars($owner['name']) ?></td>
                        <td><?= htmlspecialchars($owner['phone']) ?></td>
                        <td><?= htmlspecialchars($owner['bank']) ?></td>
                        <td><?= htmlspecialchars($owner['account']) ?></td>
                        <td><?= htmlspecialchars($owner['houses_posted']) ?></td>
                        <td><?= htmlspecialchars($owner['houses_approved']) ?></td>
                        <td><?= htmlspecialchars($owner['houses_rented']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <form method="POST" class="report-form">
                <label for="subject">Subject:</label><br>
                <input type="text" name="subject" id="subject" required><br><br>

                <label for="description">Description:</label><br>
                <textarea name="description" id="description" rows="5" required></textarea><br><br>

                <button class="btn" type="submit">Submit Report</button>
            </form>
        </div>
    </div>
</body>

</html>