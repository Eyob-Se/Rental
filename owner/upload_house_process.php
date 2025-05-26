<?php
session_start();
require_once '../config/db.php';

// Check if the user is logged in and is an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $address = $_POST['location'] ?? '';
    $price = $_POST['price'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? 0;
    $bathrooms = $_POST['bathrooms'] ?? 0;
    $area = $_POST['area'] ?? '';
    $ownerId = $_SESSION['user_id'];

    // Image upload handling (optional)
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/house_images/';
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $imageName;
        }
    }

    // Get a random property manager
    $pmStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'property_manager' ORDER BY RAND() LIMIT 1");
    $pmStmt->execute();
    $propertyManager = $pmStmt->fetch();
    $propertyManagerId = $propertyManager ? $propertyManager['id'] : null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO houses 
                (title, description, location, price, bedrooms, bathrooms, area, image_path, status, owner_id, property_manager_id)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");
        $stmt->execute([
            $title,
            $description,
            $address,
            $price,
            $bedrooms,
            $bathrooms,
            $area,
            $imagePath,
            $ownerId,
            $propertyManagerId
        ]);

        // ✅ Redirect to dashboard with message
        $_SESSION['success'] = "House uploaded successfully and is pending approval.";
        header("Location: dashboard.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error uploading house: " . $e->getMessage();
        header("Location: dashboard.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: dashboard.php");
    exit;
}
?>
