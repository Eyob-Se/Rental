<?php
session_start();
require_once '../config/db.php';

/* if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id']; */

// Get role
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$role = $stmt->fetchColumn();

if (!in_array($role, ['tenant', 'owner'])) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lease_id = $_POST['lease_id'] ?? null;

    if (!$lease_id) {
        die('Lease ID is required.');
    }

    // Fetch lease details
    $stmt = $pdo->prepare("SELECT * FROM lease_agreements WHERE id = ?");
    $stmt->execute([$lease_id]);
    $lease = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lease) {
        die('Lease agreement not found.');
    }

    // Verify access
    if ($role === 'tenant' && $lease['tenant_id'] != $user_id) {
        die('Access denied.');
    }
    if ($role === 'owner' && $lease['owner_id'] != $user_id) {
        die('Access denied.');
    }

    $uploadDir = '../leases/';
    $filePath = null;

    // Handle PDF file upload
    if (isset($_FILES['signed_file']) && $_FILES['signed_file']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($_FILES['signed_file']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['signed_file']['tmp_name'], $targetFilePath)) {
            $filePath = $targetFilePath;
        } else {
            die("Failed to upload PDF.");
        }
    }

    // Handle canvas signature image
    if (!$filePath && !empty($_POST['signature_image'])) {
        $imageData = explode(',', $_POST['signature_image'])[1];
        $imagePath = $uploadDir . 'signature_' . time() . '.png';

        if (file_put_contents($imagePath, base64_decode($imageData))) {
            $filePath = $imagePath;
        } else {
            die("Failed to save signature.");
        }
    }

    // Update lease agreement
    if ($role === 'tenant') {
        $sql = "UPDATE lease_agreements SET signed_by_tenant = 1";
    } else {
        $sql = "UPDATE lease_agreements SET signed_by_owner = 1";
    }

    if ($filePath) {
        $sql .= ", file_path = ?";
        $stmt = $pdo->prepare($sql . " WHERE id = ?");
        $stmt->execute([$filePath, $lease_id]);
    } else {
        $stmt = $pdo->prepare($sql . " WHERE id = ?");
        $stmt->execute([$lease_id]);
    }

    echo "Lease agreement signed successfully.";
    exit;
}
?>

<!-- HTML Form with Canvas -->
<form method="post" enctype="multipart/form-data" onsubmit="return prepareSignature();">
    <label>Lease ID: <input type="number" name="lease_id" required></label><br><br>

    <label>Upload Signed Lease PDF (optional):</label>
    <input type="file" name="signed_file" accept="application/pdf"><br><br>

    <label>Or sign digitally:</label><br>
    <canvas id="signature-pad" width="400" height="150" style="border:1px solid #ccc;"></canvas><br>
    <button type="button" onclick="clearSignature()">Clear</button><br><br>

    <input type="hidden" name="signature_image" id="signature-image">
    <button type="submit">Sign Lease Agreement</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas);

    function clearSignature() {
        signaturePad.clear();
    }

    function prepareSignature() {
        if (!signaturePad.isEmpty()) {
            document.getElementById('signature-image').value = signaturePad.toDataURL();
        }
        return true;
    }
</script>
