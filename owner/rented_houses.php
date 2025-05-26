<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->fetchColumn() !== 'owner') {
        die("Access denied.");
    }

    // Get rented houses of this owner along with tenant info
    $sql = "SELECT h.id AS house_id, h.title, h.price, r.id AS request_id, r.status AS request_status,
            t.name AS tenant_name, t.email AS tenant_email
            FROM houses h
            JOIN rental_requests r ON h.id = r.house_id
            JOIN users t ON r.tenant_id = t.id
            WHERE h.owner_id = ? AND h.status = 'rented' AND r.status = 'approved'
            ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $rented_houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rented Houses</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <h1>Rented Houses</h1>
    <a href="dashboard.php">Back to Dashboard</a>

    <?php if (count($rented_houses) === 0): ?>
        <p>No houses are currently rented.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>House Title</th>
                    <th>Price</th>
                    <th>Tenant Name</th>
                    <th>Tenant Email</th>
                    <th>Request Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rented_houses as $rh): ?>
                    <tr>
                        <td><?=htmlspecialchars($rh['title'])?></td>
                        <td>$<?=number_format($rh['price'], 2)?></td>
                        <td><?=htmlspecialchars($rh['tenant_name'])?></td>
                        <td><?=htmlspecialchars($rh['tenant_email'])?></td>
                        <td><?=htmlspecialchars($rh['request_status'])?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
