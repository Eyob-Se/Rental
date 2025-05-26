<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Invalid request method.";
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

if (!isset($_POST['lease_id']) || !isset($_FILES['pdf_file'])) {
    http_response_code(400);
    echo "Missing data.";
    exit;
}

$leaseId = intval($_POST['lease_id']);
$tenantId = $_SESSION['user_id'];

// Verify lease exists and belongs to this tenant
$stmt = $pdo->prepare("SELECT * FROM lease_agreements WHERE id = ? AND tenant_id = ?");
$stmt->execute([$leaseId, $tenantId]);
$lease = $stmt->fetch();

if (!$lease) {
    http_response_code(404);
    echo "Lease not found or you do not have permission.";
    exit;
}

// Save uploaded PDF file
$uploadDir = '../leases/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$houseId = $lease['house_id'];
$ownerId = $lease['owner_id'];
$timestamp = time();
$fileName = "lease_{$houseId}_{$tenantId}_{$timestamp}.pdf";
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $filePath)) {
    http_response_code(500);
    echo "Failed to save PDF file.";
    exit;
}

// Store relative file path in DB, update signed info
$relativePath = "leases/$fileName";

$update = $pdo->prepare("UPDATE lease_agreements SET
    signed_by_tenant = 1,
    signed_at = NOW(),
    file_path = ?,
    status = 'signed'
    WHERE id = ?");

$update->execute([$relativePath, $leaseId]);

echo "Lease signed and PDF stored successfully.";
