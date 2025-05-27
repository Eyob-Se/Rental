<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['house_id']) || !is_numeric($_POST['house_id'])) {
    die("Invalid house ID.");
}

$tenant_id = $_SESSION['user_id'];
$house_id = (int)$_POST['house_id'];

// Check if the house exists and is approved
// Check if the house exists and is approved
$stmt = $pdo->prepare("SELECT * FROM houses WHERE id = ? AND status = 'approved'");
$stmt->execute([$house_id]);
$house = $stmt->fetch();

if (!$house) {
    die("House not found or not available for rent.");
}

$propertyManagerId = $house['property_manager_id']; // use this instead of owner_id

if (!$propertyManagerId) {
    die("No property manager assigned to this house.");
}

// Check if this tenant has already requested this house
$stmt = $pdo->prepare("SELECT * FROM rental_requests WHERE house_id = ? AND tenant_id = ?");
$stmt->execute([$house_id, $tenant_id]);
$existing_request = $stmt->fetch();

if ($existing_request) {
    echo "<script>alert('You have already requested to rent this house.'); window.location.href = 'view_houses.php';</script>";
    exit;
}

// Insert rental request
$insertRequest = $pdo->prepare("INSERT INTO rental_requests (tenant_id, house_id, status, created_at)
                                VALUES (?, ?, 'pending', NOW())");
$insertRequest->execute([$tenant_id, $house_id]);

// request_rent.php (before inserting)
$check = $pdo->prepare("SELECT status FROM houses WHERE id = ?");
$check->execute([$house_id]);
$house = $check->fetch();

if ($house['status'] === 'rented') {
    $_SESSION['error'] = "This house is already rented.";
    header("Location: view_houses.php");
    exit;
}


// Send notification to Property Manager
$message = "I would like to rent this house.";
$stmt = $pdo->prepare("INSERT INTO notifications (receiver_id, sender_id, message, type, status, house_id)
                       VALUES (?, ?, ?, 'request', 'pending', ?)");
$success = $stmt->execute([$propertyManagerId, $tenant_id, $message, $house_id]);
if ($success) {
    echo "<script>alert('Rent request submitted successfully!'); window.location.href = 'view_houses.php';</script>";
} else {
    echo "<script>alert('Failed to submit rent request. Please try again later.'); window.location.href = 'view_houses.php';</script>";
}
?>