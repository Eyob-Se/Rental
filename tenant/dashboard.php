<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rental_requests WHERE tenant_id = ?");
    $stmt->execute([$user_id]);
    $requests_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE tenant_id = ?");
    $stmt->execute([$user_id]);
    $payments_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="../assets/style1.css" />
    <link rel="stylesheet" href="../assets/fonts/all.css" />
</head>
<body>

<div class="prop_con">

    <div class="navbar prop_nav">
        <p>Rental.</p>
        <ul>
            <li><a href="view_houses.php">View Available Houses</a></li>
            <li><a href="my_requests.php">My Requests</a></li>
            <li><a href="my_payments.php">My Payments</a></li>
            <li><a href="lease_agreements.php">Lease Agreements</a></li>
        </ul>
        <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>
      <div class="container">    
              <h1 style= "color: #b2b2b2;">Welcome, Tenant</h1>
              <p  style= "color: #ffffff;">You have submitted <strong><?= htmlspecialchars($requests_count) ?></strong> rent requests.</p>
              <p  style= "color: #ffffff;">You have made <strong><?= htmlspecialchars($payments_count) ?></strong> payments.</p>
      </div>

 <footer>
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
