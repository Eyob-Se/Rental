<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = $_SESSION['user_id'];
    $house_id = $_POST['house_id'];
    $request_id = $_POST['request_id'];
    $amount = $_POST['amount']; // total amount including fee

    // Validate uploaded file
    if (!isset($_FILES['receipt_file']) || $_FILES['receipt_file']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>
                alert('Error uploading receipt.');
                window.location.href = 'my_requests.php';
              </script>";
        exit;
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    $file_tmp = $_FILES['receipt_file']['tmp_name'];
    $file_name = basename($_FILES['receipt_file']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        echo "<script>
                alert('Invalid file type. Allowed: jpg, jpeg, png, pdf.');
                window.location.href = 'my_requests.php';
              </script>";
        exit;
    }

    // Create uploads directory if not exists
    $upload_dir = '../uploads/receipts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique file name
    $new_file_name = uniqid('receipt_') . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    if (!move_uploaded_file($file_tmp, $destination)) {
        echo "<script>
                alert('Failed to move uploaded file.');
                window.location.href = 'my_requests.php';
              </script>";
        exit;
    }

    // Calculate fee (10%) and base amount
    $fee = round($amount * 0.10 / 1.10, 2); // fee is 10% of base rent, total = rent + fee
    $base_amount = round($amount - $fee, 2);

    // Insert into transactions table
    $stmt = $pdo->prepare("INSERT INTO transactions (tenant_id, house_id, amount, fee, total, status, file_path) VALUES (?, ?, ?, ?, ?, 'unverified', ?)");
    $stmt->execute([$tenant_id, $house_id, $base_amount, $fee, $amount, $new_file_name]);

    // Fetch property_manager_id of the house
    $stmt_pm = $pdo->prepare("SELECT property_manager_id FROM houses WHERE id = ?");
    $stmt_pm->execute([$house_id]);
    $pm_id = $stmt_pm->fetchColumn();

    if (!$pm_id) {
        // Fallback PM ID (change as needed)
        $pm_id = 1;
    }

    // Insert notification for the PM
    $message = "New unverified payment receipt uploaded for House ID: $house_id.";
    $stmt_notify = $pdo->prepare("INSERT INTO notifications (sender_id, receiver_id, message, is_read, type, status, house_id) VALUES (?, ?, ?, 0, 'request', 'unverified', ?)");
    $stmt_notify->execute([$tenant_id, $pm_id, $message, $house_id]);

    // Show success alert and redirect back to requests page
    echo "<script>
            alert('Receipt uploaded successfully and pending verification.');
            window.location.href = 'my_requests.php';
          </script>";
    exit;
}
?>
