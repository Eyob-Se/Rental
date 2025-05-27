<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['agree_terms'])) {
        die("You must agree to terms and privacy policy.");
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $bank_account = null;
    $payment_method = null;

    if ($role === 'owner') {
        $bank_account = isset($_POST['bank_account']) ? trim($_POST['bank_account']) : null;
        $payment_method = isset($_POST['preferred_payment_method']) ? trim($_POST['preferred_payment_method']) : null;

        if (empty($bank_account) || empty($payment_method)) {
            die("Bank account and payment method are required for owners.");
        }
    }

    if (!in_array($role, ['tenant', 'owner'])) {
        die("Invalid role selected.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['id_photo']['tmp_name'];
        $fileName = $_FILES['id_photo']['name'];
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
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role]);
        $user_id = $pdo->lastInsertId();

        if ($role === 'tenant') {
            $stmt = $pdo->prepare("INSERT INTO tenant_profiles (user_id, phone, address, id_photo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $phone, $address, $newFileName]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO owner_profiles (user_id, phone, address, id_photo, account, bank) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $phone, $address, $newFileName, $bank_account, $payment_method]);
        }

        // SweetAlert + redirect
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Success</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Registered successfully!',
                    text: 'You will be redirected to login shortly.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = 'login.php';
                });
            </script>
        </body>
        </html>
        ";
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
