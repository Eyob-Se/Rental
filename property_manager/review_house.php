<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['house_id'], $_POST['action'])) {
    $house_id = intval($_POST['house_id']);
    $status = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';

    $stmt = $pdo->prepare("UPDATE houses SET status = ? WHERE id = ?");
    $stmt->execute([$status, $house_id]);
}

$houses = $pdo->query("SELECT * FROM houses WHERE status = 'pending'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Review Houses</title>
    <link rel="stylesheet" href="../assets/style1.css" />
    <link rel="stylesheet" href="../assets/fonts/all.css" />
</head>
<body>
<div class="prop_con">

    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="review_request.php">Requests</a></li>
                <li><a href="review_house.php">Approvals</a></li>
                <li><a href="lease-agreements.php">Agreements</a></li>
                <li><a href="generate_report.php">Reports</a></li>
        </ul>
        
        <button class="btn"  onclick="window.location.href='../auth/logout.php'">Logout</button>
        
    </div>
        <button class="btn" style="margin: 2rem 0rem 0rem 18rem;" onclick="window.location.href='dashboard.php'">Back to dashboard</button>

    <div class="cards">

        
        <?php if ($houses): ?>
            <?php foreach ($houses as $house): ?>
                <div class="card">
                    
                    <img src="../uploads/house_images/<?= htmlspecialchars($house['image_path'] ?: 'default.jpg') ?>" alt="<?= htmlspecialchars($house['title']) ?>">
                    <h3><?= htmlspecialchars($house['title']) ?></h3>
                    <h4><i class="fas fa-location-dot"></i> <?= htmlspecialchars($house['location']) ?></h4>
                    <div class="spec">
                        <p>Bedroom<br><i class="fas fa-bed"></i> <?= (int)$house['bedrooms'] ?></p>
                        <p>Bathroom<br><i class="fas fa-shower"></i> <?= (int)$house['bathrooms'] ?></p>
                        <p>Area<br><i class="fas fa-ruler-combined"></i> <?= htmlspecialchars($house['area']) ?> sqft</p>
                    </div>
                    <p class="price">$<?= number_format($house['price'], 2) ?>/month</p>
                    <form method="POST" class="approval-buttons">
                        <input type="hidden" name="house_id" value="<?= (int)$house['id'] ?>">
                        <button name="action" value="approve" class="btn approve">Approve</button>
                        <button name="action" value="reject" class="btn reject">Reject</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; margin: 2rem auto;">No pending houses for review.</p>
        <?php endif; ?>
    </div>

    <footer style="position: absolute; bottom: 0px; width: 100%;">
    <div class="footer">
      <div class="footer-container">
        <div class="footer-top">
          <p style=" color: #2b2d42;">&copy; <?= date("Y") ?> Rental System. All rights reserved.</p>
          <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms</a>
            <a href="#">Contact</a>
          </div>
        </div>

        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-x-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>

        <div class="footer-newsletter">
          <form action="#" method="post">
            <input type="email" placeholder="Your email address" required>
            <button type="submit"><i class="fa fa-paper-plane"></i> Subscribe</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</footer>

</div>
</body>
</html>
