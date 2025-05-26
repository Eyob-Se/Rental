<?php
session_start();
require_once '../config/db.php';
require_once '../config/chapa.php'; // Chapa API credentials

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$tenant_id = $_SESSION['user_id'];
$lease_id = $_POST['lease_id'];
$amount = floatval($_POST['amount']);
$service_fee = round($amount * 0.02, 2);
$total = $amount + $service_fee;
$method = $_POST['method'];

$reference = 'CHP_' . uniqid();
$callback_url = "https://yourdomain.com/tenant/payment_callback.php"; // Must match Chapa dashboard

$stmt = $pdo->prepare("INSERT INTO tenant_payment (tenant_id, lease_id, amount, service_fee, method, reference_id, status) 
                       VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$tenant_id, $lease_id, $amount, $service_fee, $method, $reference]);

// Redirect to Chapa payment
$payload = [
    'amount' => $total,
    'currency' => 'ETB',
    'email' => $_SESSION['email'],
    'first_name' => $_SESSION['name'],
    'tx_ref' => $reference,
    'callback_url' => $callback_url,
    'return_url' => $callback_url,
    'customization' => [
        'title' => 'Rental Payment',
        'description' => 'Monthly rental payment'
    ]
];

$ch = curl_init("https://api.chapa.co/v1/transaction/initialize");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . CHAPA_SECRET_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $result = json_decode($response, true);
    header("Location: " . $result['data']['checkout_url']);
    exit;
} else {
    die("Failed to initiate payment.");
}
