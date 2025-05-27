<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

// Allow only property managers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = $_POST['transaction_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'verify' or 'reject'

    if (!$transactionId || !in_array($action, ['verify', 'reject'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }

    // Fetch the transaction
    $stmt = $pdo->prepare("SELECT t.*, h.title AS house_title, h.id AS house_id, h.owner_id, u.name AS tenant_name 
                           FROM transactions t 
                           JOIN houses h ON t.house_id = h.id 
                           JOIN users u ON t.tenant_id = u.id 
                           WHERE t.id = ?");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
        exit;
    }

    if ($transaction['status'] !== 'unverified') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Transaction already processed']);
        exit;
    }

    if ($action === 'verify') {
        // 1. Update transaction status
        $pdo->prepare("UPDATE transactions SET status = 'verified' WHERE id = ?")->execute([$transactionId]);

        // 2. Mark house as rented
        $pdo->prepare("UPDATE houses SET is_rented = 1 WHERE id = ?")->execute([$transaction['house_id']]);

        // 3. Create lease agreement
        $terms = "This lease agreement is made between the owner and the tenant. Tenant: {$transaction['tenant_name']}. House: {$transaction['house_title']}. Duration: 12 months. Rent: {$transaction['amount']} USD.";
        $leaseStmt = $pdo->prepare("INSERT INTO lease_agreements (house_id, tenant_id, owner_id, signed_by_tenant, signed_by_owner, file_path, created_at, status) 
                                    VALUES (?, ?, ?, 0, 1, NULL, NOW(), 'pending')");
        $leaseStmt->execute([
            $transaction['house_id'],
            $transaction['tenant_id'],
            $transaction['owner_id']
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Payment verified and lease generated. Tenant notified.']);
        exit;

    } elseif ($action === 'reject') {
        // Mark transaction as rejected
        $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ?")->execute([$transactionId]);

        echo json_encode(['status' => 'success', 'message' => 'Payment rejected']);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}