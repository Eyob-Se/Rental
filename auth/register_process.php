<?php
// auth/register_process.php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['agree_terms'])) {
        die("You must agree to terms and privacy policy.");
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // tenant or owner
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validate role
    if (!in_array($role, ['tenant', 'owner'])) {
        die("Invalid role selected.");
    }

    // Password hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle ID photo upload
    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['id_photo']['tmp_name'];
        $fileName = $_FILES['id_photo']['name'];
        $fileSize = $_FILES['id_photo']['size'];
        $fileType = $_FILES['id_photo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($fileExtension, $allowedfileExtensions)) {
            die("Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions));
        }

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../uploads/id_photos/';
        if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);
        $dest_path = $uploadFileDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $dest_path)) {
            die("Error moving uploaded file.");
        }
    } else {
        die("ID photo upload error.");
    }

    try {
        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role]);
        $user_id = $pdo->lastInsertId();

        // Insert into profile table
        if ($role === 'tenant') {
            $stmt = $pdo->prepare("INSERT INTO tenant_profiles (user_id, phone, address, id_photo) VALUES (?, ?, ?, ?)");
        } else { // owner
            $stmt = $pdo->prepare("INSERT INTO owner_profiles (user_id, phone, address, id_photo) VALUES (?, ?, ?, ?)");
        }
        $stmt->execute([$user_id, $phone, $address, $newFileName]);

        $_SESSION['success'] = "Registration successful. You can now login.";
        header("Location: login.php");
        exit;

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            die("Email already exists.");
        }
        die("Error: " . $e->getMessage());
    }

} else {
    die("Invalid request.");
}
?>
