<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tenant_payment_form.php");
    exit;
}

$house_id = $_POST['house_id'] ?? '';
$amount = $_POST['amount'] ?? 0;
$final_amount = $_POST['final_amount'] ?? 0;

// Validate inputs
if (empty($house_id) || $amount <= 0 || $final_amount <= 0) {
    die("Invalid input.");
}

// Convert to cents (Chapa expects amount in cents)
$amount_cents = (int)round($final_amount * 100);

// Chapa API details
$chapa_secret_key = 'YOUR_CHAPA_SECRET_KEY'; // Replace with your real secret key
$chapa_callback_url = 'https://yourdomain.com/tenant/chapa_callback.php'; // Change to your callback URL

// Generate unique transaction reference
$tx_ref = 'TX-' . time() . '-' . $user_id . '-' . rand(1000, 9999);

// Prepare payload for Chapa
$data = [
    "amount" => $final_amount,
    "currency" => "ETB",
    "email" => "",  // optionally fetch from user profile if you store email
    "first_name" => "", // fetch user first name if available
    "last_name" => "", // fetch user last name if available
    "callback_url" => $chapa_callback_url,
    "tx_ref" => $tx_ref,
    "metadata" => [
        "user_id" => $user_id,
        "house_id" => $house_id,
        "amount" => $amount,
        "service_fee" => $final_amount - $amount
    ]
];

// Initialize cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.chapa.co/v1/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $chapa_secret_key"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("cURL Error #: " . $err);
}

$res = json_decode($response, true);

if (!$res || !isset($res['data']['checkout_url'])) {
    die("Payment initialization failed. Please try again later.");
}

// Optional: Save transaction record in your DB here with status = 'pending'

header("Location: " . $res['data']['checkout_url']);
exit;
