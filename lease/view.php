<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch leases related to this user (tenant or owner)
$stmt = $pdo->prepare("
    SELECT la.*, u1.name AS tenant_name, u2.name AS owner_name 
    FROM lease_agreements la
    JOIN users u1 ON la.tenant_id = u1.id
    JOIN users u2 ON la.owner_id = u2.id
    WHERE la.tenant_id = ? OR la.owner_id = ?
");
$stmt->execute([$user_id, $user_id]);
$leases = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Your Lease Agreements</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>House ID</th>
        <th>Tenant</th>
        <th>Owner</th>
        <th>Signed by Tenant</th>
        <th>Signed by Owner</th>
        <th>Lease File</th>
        <th>Created At</th>
    </tr>
    <?php foreach ($leases as $lease): ?>
    <tr>
        <td><?= htmlspecialchars($lease['id']) ?></td>
        <td><?= htmlspecialchars($lease['house_id']) ?></td>
        <td><?= htmlspecialchars($lease['tenant_name']) ?></td>
        <td><?= htmlspecialchars($lease['owner_name']) ?></td>
        <td><?= $lease['signed_by_tenant'] ? 'Yes' : 'No' ?></td>
        <td><?= $lease['signed_by_owner'] ? 'Yes' : 'No' ?></td>
        <td>
            <?php if ($lease['file_path'] && file_exists($lease['file_path'])): ?>
            <a href="<?= htmlspecialchars($lease['file_path']) ?>" target="_blank">View PDF</a>
            <?php else: ?>
            No file uploaded
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($lease['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>