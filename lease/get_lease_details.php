<?php
require_once '../config/db.php';

if (!isset($_GET['lease_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing lease ID']);
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
        echo json_encode(['error' => 'Lease not found']);
        exit;
    }

    // Build the lease terms dynamically
    $terms = "
        <p>This lease agreement is made between <strong>{$lease['tenant_name']}</strong> and the property manager for the rental of the property located at <strong>{$lease['house_address']}</strong>.</p>
        <p>The lease is effective starting from the payment date of <strong>" . date('F j, Y', strtotime($lease['payment_date'])) . "</strong>.</p>
        <p>The tenant agrees to abide by the property rules, make timely rent payments, and maintain the property in good condition.</p>
        <p>This agreement is valid until otherwise terminated by either party with due notice.</p>

        <hr>
        <h4>Additional Lease Terms:</h4>
        <ul>
            <li>Lease Duration: 3 months</li>
            <li>Rent: Payable every 3 months as well as updating lease</li>
            <li>Tenant must not sublet the property</li>
            <li>Property must be kept clean and damage-free</li>
        </ul>
    ";

    $tenant_id_photo_url = !empty($lease['tenant_id_photo']) 
        ? "../uploads/id_photos/" . htmlspecialchars($lease['tenant_id_photo']) 
        : null;

    echo json_encode([
        'lease_text' => $terms,
        'tenant_id_photo_url' => $tenant_id_photo_url
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "Database error: " . $e->getMessage()]);
}
