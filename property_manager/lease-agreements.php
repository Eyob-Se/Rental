<?php
session_start();
require_once '../config/db.php';

// Check if user is property manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all leases signed by tenant and owner, with their contact info
$stmt = $pdo->prepare("
    SELECT 
        la.id AS lease_id, 
        la.file_path, 
        la.signed_at, 
        h.title AS house_title, 
        
        tenant.name AS tenant_name, 
        tenant.email AS tenant_email, 
        tp.phone AS tenant_phone,
        
        owner.name AS owner_name, 
        owner.email AS owner_email, 
        op.phone AS owner_phone
        
    FROM lease_agreements la
    JOIN houses h ON la.house_id = h.id
    JOIN users tenant ON la.tenant_id = tenant.id
    JOIN tenant_profiles tp ON tp.user_id = tenant.id
    
    JOIN users owner ON la.owner_id = owner.id
    JOIN owner_profiles op ON op.user_id = owner.id
    
    WHERE la.signed_by_tenant = 1 AND la.signed_by_owner = 1
    ORDER BY la.signed_at DESC
");
$stmt->execute();
$signedLeases = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Signed Leases - Property Manager</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
<div class="prop_con">
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="../property_manager/dashboard.php">Dashboard</a></li>
            <li><a href="signed_leases.php" class="active">Signed Leases</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <section>
            <h3>Signed Lease Agreements</h3>

            <?php if (empty($signedLeases)): ?>
                <p>No signed leases found.</p>
            <?php else: ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Lease ID</th>
                            <th>House</th>
                            <th>Tenant</th>
                            <th>Tenant Phone</th>
                            <th>Tenant Email</th>
                            <th>Owner</th>
                            <th>Owner Phone</th>
                            <th>Owner Email</th>
                            <th>Signed Date</th>
                            <th>PDF File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($signedLeases as $lease): ?>
                            <tr>
                                <td><?= htmlspecialchars($lease['lease_id']) ?></td>
                                <td><?= htmlspecialchars($lease['house_title']) ?></td>
                                <td><?= htmlspecialchars($lease['tenant_name']) ?></td>
                                <td><?= htmlspecialchars($lease['tenant_phone']) ?></td>
                                <td><?= htmlspecialchars($lease['tenant_email']) ?></td>
                                <td><?= htmlspecialchars($lease['owner_name']) ?></td>
                                <td><?= htmlspecialchars($lease['owner_phone']) ?></td>
                                <td><?= htmlspecialchars($lease['owner_email']) ?></td>
                                <td><?= date("F j, Y, g:i a", strtotime($lease['signed_at'])) ?></td>
                                <td>
                                    <?php if ($lease['file_path'] && file_exists("../" . $lease['file_path'])): ?>
                                        <button class="btn">
                                            <a href="../<?= htmlspecialchars($lease['file_path']) ?>" target="_blank" download>Get PDF</a>
                                        </button>
                                    <?php else: ?>
                                        <em>No file available</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <br />
            <a href="../property_manager/dashboard.php" class="btn">Back to Dashboard</a>
        </section>
    </div>
</div>
</body>
</html>
