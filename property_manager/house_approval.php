<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and role = property_manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header('Location: ../auth/login.php');
    exit;
}

// Handle approve/decline actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['house_id'], $_POST['action'])) {
    $houseId = intval($_POST['house_id']);
    $action = $_POST['action']; // 'approve' or 'decline'

    if (!in_array($action, ['approve', 'decline'])) {
        die('Invalid action');
    }

    $newStatus = $action === 'approve' ? 'approved' : 'declined';

    // Update house status
    $stmt = $pdo->prepare("UPDATE houses SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $houseId]);

    // Get owner id of this house
    $stmt2 = $pdo->prepare("SELECT owner_id, title FROM houses WHERE id = ?");
    $stmt2->execute([$houseId]);
    $house = $stmt2->fetch();

if ($house) {
    $ownerId = $house['owner_id'];
    $houseTitle = $house['title'];

    // Insert notification for owner
    $message = "Your house '{$houseTitle}' has been " . ($newStatus === 'approved' ? 'approved' : 'declined') . " by Property Manager.";
    $stmt3 = $pdo->prepare("INSERT INTO notifications (sender_id, receiver_id, message, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
    $stmt3->execute([$_SESSION['user_id'], $ownerId, $message]);
}

    $_SESSION['success'] = "House has been {$newStatus}.";
    header("Location: house_approval.php");
    exit;
}

// Fetch all pending houses
$stmt = $pdo->prepare("SELECT h.id, h.title, h.description, h.address, h.price, h.image_path, u.name AS owner_name 
                       FROM houses h 
                       JOIN users u ON h.owner_id = u.id
                       WHERE h.status = 'pending'");
$stmt->execute();
$houses = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Property Manager - House Approvals</title>
</head>
<body>
<div class="container">
              <button class="btn" onclick="window.location.href='dashboard.php'">Back to dashboard</button>

  <h1>Pending House Approvals</h1>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="message"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (count($houses) === 0): ?>
    <p>No pending houses for approval.</p>
  <?php else: ?>
    <?php foreach ($houses as $house): ?>
      <div class="house-card">
        <img src="../uploads/house_images/<?= htmlspecialchars($house['image_path']) ?>" alt="House Image" class="house-image" />
        <div class="house-info">
          <h3><?= htmlspecialchars($house['title']) ?></h3>
          <p><strong>Owner:</strong> <?= htmlspecialchars($house['owner_name']) ?></p>
          <p><strong>Address:</strong> <?= htmlspecialchars($house['address']) ?></p>
          <p><strong>Price:</strong> $<?= number_format($house['price'], 2) ?></p>
          <p><?= nl2br(htmlspecialchars($house['description'])) ?></p>
          <form method="POST" class="actions">
            <input type="hidden" name="house_id" value="<?= $house['id'] ?>">
            <button type="submit" name="action" value="approve" class="approve-btn" onclick="return confirm('Approve this house?');">Approve</button>
            <button type="submit" name="action" value="decline" class="decline-btn" onclick="return confirm('Decline this house?');">Decline</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body>
</html>
