<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

// Check if logged in and has permission (e.g., government role)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user is government role (adjust role check to your setup)
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$role = $stmt->fetchColumn();
if ($role !== 'government') {
    die("Access denied.");
}

// Process POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_POST['owner_id'] ?? null;
    $message = trim($_POST['message'] ?? '');

    if (!$owner_id || empty($message)) {
        die("Owner and message are required.");
    }

    // Optional: Validate owner exists and is an owner
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'owner'");
    $stmt->execute([$owner_id]);
    if (!$stmt->fetchColumn()) {
        die("Invalid owner selected.");
    }

    // Insert tax notice into notifications table
    $insert = $pdo->prepare("
        INSERT INTO notifications (sender_id, receiver_id, type, message, created_at)
        VALUES (?, ?, 'government_notice', ?, NOW())
    ");
    $insert->execute([$user_id, $owner_id, $message]);

    // Redirect back with success message or just back to reports page
    header("Location: view_reports.php?sent=1");
    exit;
}

die("Invalid request.");
