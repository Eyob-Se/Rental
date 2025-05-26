<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$tenantId = $_SESSION['user_id'];

// Fetch approved houses
$stmt = $pdo->prepare("SELECT * FROM houses WHERE status = 'approved'");
$stmt->execute();
$houses = $stmt->fetchAll();

// Fetch tenant's rental requests with status
$reqStmt = $pdo->prepare("SELECT house_id, status FROM rental_requests WHERE tenant_id = ?");
$reqStmt->execute([$tenantId]);
$requestedHouses = $reqStmt->fetchAll(PDO::FETCH_KEY_PAIR); 
// returns array like [house_id => status, ...]
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../assets/style1.css" />
  <link rel="stylesheet" href="../assets/fonts/all.css" />
  <title>Available Houses</title>
  <style>
    .approved {
        background-color: #28a745;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
    }
    .declined {
        background-color: #e74c3c;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
    }
    .requested {
        background-color: gray;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="prop_con">
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="view_houses.php">View Available Houses</a></li>
            <li><a href="my_requests.php">My Requests</a></li>
            <li><a href="my_payments.php">My Payments</a></li>
            <li><a href="lease_agreements.php">Lease Agreements</a></li>
        </ul>
        <button><a href="../auth/logout.php">Logout</a></button>
    </div>

    <div class="cards">
      <?php foreach ($houses as $house): ?>
        <div class="card">
          <img src="../uploads/house_images/<?= htmlspecialchars($house['image_path']) ?>" alt="<?= htmlspecialchars($house['title']) ?>" />
          <h3><?= htmlspecialchars($house['title']) ?></h3>
          <h4><i class="fas fa-location-dot"></i> <?= htmlspecialchars($house['location']) ?></h4>
          <div class="spec">
              <p>Bedrooms<br><i class="fas fa-bed"></i> <?= (int)$house['bedrooms'] ?></p>
              <p>Bathrooms<br><i class="fas fa-shower"></i> <?= (int)$house['bathrooms'] ?></p>
              <p>Area<br><i class="fas fa-ruler-combined"></i> <?= htmlspecialchars($house['area']) ?> sq ft</p>
          </div>
          <p class="price">$<?= number_format($house['price'], 2) ?></p>

<?php if (isset($requestedHouses[$house['id']])): 
    $status = $requestedHouses[$house['id']];
    if ($status === 'approved'): ?>
        <p class="btn approved">Approved</p>
    <?php elseif ($status === 'declined'): ?>
        <p class="btn declined">Declined</p>
    <?php else: ?>
        <p class="btn requested"><?= ucfirst($status) ?></p>
    <?php endif; ?>
<?php else: ?>
    <form action="request_rent.php" method="post" style="display:inline;">
        <input type="hidden" name="house_id" value="<?= (int)$house['id'] ?>">
        <button type="submit" class="btn">Request to Rent</button>
    </form>
<?php endif; ?>
        </div>
      <?php endforeach; ?>
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
    </footer>
  </div>
</body>
</html>
