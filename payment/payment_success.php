<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['tx_ref'])) {
    echo "No transaction reference provided.";
    exit;
}

$tx_ref = $_GET['tx_ref'];

// Verify with Chapa API
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.chapa.co/v1/transaction/verify/" . $tx_ref);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer YOUR_CHAPA_SECRET_KEY" // Replace with your key
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data && $data['status'] === 'success') {
    echo "<h2>✅ Payment Successful!</h2>";
    echo "<p>Thank you for your payment, " . htmlspecialchars($data['data']['customer']['first_name']) . ".</p>";
    echo "<p>Transaction Reference: " . htmlspecialchars($tx_ref) . "</p>";
} else {
    echo "<h2>❌ Payment Verification Failed</h2>";
    echo "<p>We couldn't verify your transaction. Please contact support.</p>";
}
