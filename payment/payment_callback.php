<?php
require_once '../config/db.php';

if (isset($_GET['tx_ref'])) {
    $ref = $_GET['tx_ref'];

    $verify_url = "https://api.chapa.co/v1/transaction/verify/" . $ref;
    $ch = curl_init($verify_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . CHAPA_SECRET_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($data['status'] === 'success' && $data['data']['status'] === 'success') {
        $stmt = $pdo->prepare("UPDATE tenant_payment SET status = 'completed' WHERE reference_id = ?");
        $stmt->execute([$ref]);
        echo "Payment successful!";
    } else {
        $stmt = $pdo->prepare("UPDATE tenant_payment SET status = 'failed' WHERE reference_id = ?");
        $stmt->execute([$ref]);
        echo "Payment failed.";
    }
} else {
    echo "Invalid transaction.";
}
