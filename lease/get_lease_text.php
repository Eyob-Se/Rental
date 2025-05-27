<?php
require_once '../config/db.php';

if (!isset($_GET['lease_id'])) {
    http_response_code(400);
    echo "Missing lease ID";
    exit;
}

$lease_id = $_GET['lease_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            la.id AS lease_id,
            la.created_at AS lease_date,
            h.title AS house_title,
            h.location AS house_address,
            u.name AS tenant_name,
            u.email AS tenant_email,
            tp.id_photo AS tenant_id_photo,
            t.payment_date
        FROM lease_agreements la
        JOIN houses h ON la.house_id = h.id
        JOIN users u ON la.tenant_id = u.id
        JOIN tenant_profiles tp ON tp.user_id = la.tenant_id
        JOIN transactions t ON t.house_id = h.id AND t.tenant_id = u.id
        WHERE la.id = ?
    ");
    $stmt->execute([$lease_id]);
    $lease = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lease) {
        http_response_code(404);
        echo "Lease not found.";
        exit;
    }

    // Hardcoded lease terms
    $terms = "
        <p>This lease agreement is made between <strong>{$lease['tenant_name']}</strong> and the property manager for the rental of the property located at <strong>{$lease['house_address']}</strong>.</p>
        <p>The lease is effective starting from the payment date of <strong>" . date('F j, Y', strtotime($lease['payment_date'])) . "</strong>.</p>
        <p>The tenant agrees to abide by the property rules, make timely rent payments, and maintain the property in good condition.</p>
        <p>This agreement is valid until otherwise terminated by either party with due notice.</p>
    ";

    // Output ONLY the inner content for the modal, no buttons or checkboxes here
    ?>
    <div>
        <h4>Lease Agreement for <?= htmlspecialchars($lease['house_title']) ?></h4>
        <p><strong>Tenant Name:</strong> <?= htmlspecialchars($lease['tenant_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($lease['tenant_email']) ?></p>
        <p><strong>Lease Date:</strong> <?= date("F j, Y", strtotime($lease['lease_date'])) ?></p>
        <p><strong>Payment Date:</strong> <?= date("F j, Y", strtotime($lease['payment_date'])) ?></p>
        <p><strong>ID Photo:</strong><br>
            <img src="../uploads/id_photos/<?= htmlspecialchars($lease['tenant_id_photo']) ?>" alt="Tenant ID" width="200" />
        </p>
        <hr>
        <h3>Terms and Conditions</h3>
        <h4>Lease Terms:</h4>
        <p>This lease agreement is entered into by the above tenant and the property management company.</p>
        <ul>
            <li>Lease Duration: 3 months</li>
            <li>Rent: Payable every 3 months as well as updating lease</li>
            <li>Tenant must not sublet the property</li>
            <li>Property must be kept clean and damage-free</li>
        </ul>
        <div style="margin-bottom: 20px;">
            <?= $terms ?>
        </div>
    </div>
    <?php

} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>
