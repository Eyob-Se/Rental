<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check user role is tenant
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();

    if ($role !== 'tenant') {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Read JSON POST data
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['house_id']) || empty($data['amount']) || !isset($data['tax'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'house_id, amount, and tax are required']);
    exit;
}

$house_id = intval($data['house_id']);
$amount = floatval($data['amount']);
$tax = floatval($data['tax']);
$total = $amount + $tax;

try {
    // Check if rental request was approved
    $stmt = $pdo->prepare("SELECT status FROM rental_requests WHERE tenant_id = ? AND house_id = ?");
    $stmt->execute([$user_id, $house_id]);
    $rental_status = $stmt->fetchColumn();

    if ($rental_status !== 'approved') {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Rental request not approved']);
        exit;
    }

    // Insert transaction record
    $stmt = $pdo->prepare("INSERT INTO transactions (tenant_id, house_id, amount, tax, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $house_id, $amount, $tax, $total]);

    // Update house status to 'rented'
    $stmt = $pdo->prepare("UPDATE houses SET status = 'rented' WHERE id = ?");
    $stmt->execute([$house_id]);

    // Optionally update rental request or lease agreements here

    echo json_encode(['success' => 'Payment confirmed and house rented']);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
