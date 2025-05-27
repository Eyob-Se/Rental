<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$tenantId = $_SESSION['user_id'];

// Build the filtered house query (without location)
$query = "SELECT * FROM houses WHERE status = 'approved' AND is_rented = 0";
$params = [];

if (!empty($_GET['min_price'])) {
    $query .= " AND price >= ?";
    $params[] = (float)$_GET['min_price'];
}

if (!empty($_GET['max_price'])) {
    $query .= " AND price <= ?";
    $params[] = (float)$_GET['max_price'];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$houses = $stmt->fetchAll();

// Fetch tenant's rental requests with status
$reqStmt = $pdo->prepare("SELECT house_id, status FROM rental_requests WHERE tenant_id = ?");
$reqStmt->execute([$tenantId]);
$requestedHouses = $reqStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch houses actually rented by the tenant
$rentedStmt = $pdo->prepare("
    SELECT rr.house_id 
    FROM rental_requests rr
    JOIN houses h ON rr.house_id = h.id
    WHERE rr.tenant_id = ? AND rr.status = 'approved' AND h.is_rented = 1
");
$rentedStmt->execute([$tenantId]);
$rentedHouses = $rentedStmt->fetchAll(PDO::FETCH_COLUMN);
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

    form.filter-form {
        margin: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    form.filter-form input[type="number"] {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .clear-btn {
        background-color: #777;
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 4px;
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

        <!-- Filter Form (location input removed) -->
        <form method="get" class="filter-form">
            <input type="number" name="min_price" placeholder="Min Price"
                value="<?= isset($_GET['min_price']) ? (int)$_GET['min_price'] : '' ?>" />
            <input type="number" name="max_price" placeholder="Max Price"
                value="<?= isset($_GET['max_price']) ? (int)$_GET['max_price'] : '' ?>" />
            <button type="submit" class="btn">Filter</button>
            <a href="view_houses.php" class="clear-btn">Clear</a>
        </form>

        <!-- House Cards -->
        <div class="cards">
            <?php foreach ($houses as $house): 
          if (in_array($house['id'], $rentedHouses)) continue; // Skip rented houses
      ?>
            <div class="card">
                <img src="../uploads/house_images/<?= htmlspecialchars($house['image_path']) ?>"
                    alt="<?= htmlspecialchars($house['title']) ?>" />
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

        <!-- Footer -->
        <footer>
            <div class="footer">
                <div class="footer-container">
                    <div class="footer-top">
                        <p style="color: #2b2d42;">&copy; <?= date("Y") ?> Rental System. All rights reserved.</p>
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