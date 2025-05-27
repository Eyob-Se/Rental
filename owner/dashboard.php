<?php
session_start();
if (isset($_SESSION['success'])) {
    echo "<div class='alert success'>{$_SESSION['success']}</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert error'>{$_SESSION['error']}</div>";
    unset($_SESSION['error']);
}
require_once '../config/db.php';

// Check if logged in and role is owner
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();

    if ($role !== 'owner') {
        die("Access denied.");
    }

    // Fetch houses uploaded by this owner
    $stmt = $pdo->prepare("SELECT * FROM houses WHERE owner_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // Fetch rented houses info
$stmt = $pdo->prepare("
    SELECT 
        h.title, h.location, h.price, 
        u.name AS tenant_name, 
        t.payment_date AS start_date
    FROM houses h
    INNER JOIN transactions t ON h.id = t.house_id
    INNER JOIN users u ON t.tenant_id = u.id
    WHERE h.owner_id = ? AND t.status = 'verified'
    ORDER BY t.payment_date DESC
");
$stmt->execute([$user_id]);
$rentedHouses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Owner Dashboard</title>
  <link rel="stylesheet" href="../assets/style1.css" />
  <link rel="stylesheet" href="../assets/fonts/all.css">
</head>
<body>
<div class="prop_con">

<div class="navbar prop_nav">
  <p>Rental.</p>
   <ul>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="dashboard.php">Houses</a></li>
        </ul>
  <button><a href="../auth/logout.php">Logout</a></button>
</div>

<div class="container">
  <h2>Welcome, Owner</h2>
  <p>Manage your property listings</p>

  <div class="top-bar">
    <input type="text" id="filterInput" placeholder="🔍 Filter by location, price, etc.">
    <button class="btn" id="openFormBtn">+ Upload New House</button>
  </div>

<!-- House Upload Modal -->
<div class="modal" id="houseFormModal" style="display:none;">
  <div class="modal-content">
    <span class="close" id="closeFormBtn">&times;</span>
    <h3>Upload House</h3>

    <form id="houseForm" method="POST" enctype="multipart/form-data" action="upload_house_process.php">
      <label for="title">Title</label>
      <input type="text" name="title" id="title" placeholder="House Title" required />

      <label for="location">Location</label>
      <input type="text" name="location" id="location" placeholder="City, Area" required />

      <label for="price">Price (USD)</label>
      <input type="number" name="price" id="price" step="0.01" placeholder="Monthly Rent" required />

      <label for="bedrooms">Bedrooms</label>
      <input type="number" name="bedrooms" id="bedrooms" min="0" placeholder="e.g., 2" required />

      <label for="bathrooms">Bathrooms</label>
      <input type="number" name="bathrooms" id="bathrooms" min="0" placeholder="e.g., 1" required />

      <label for="area">Area (sq ft)</label>
      <input type="number" name="area" id="area" step="0.1" placeholder="e.g., 1200" required />

      <label for="description">Description</label>
      <textarea name="description" id="description" placeholder="Brief Description" rows="3" required></textarea>

      <label for="image">Upload Image</label>
      <input type="file" name="image" id="image" accept="image/*" required />

      <button type="submit" class="btn">Submit</button>
    </form>
  </div>
</div>


  <!-- Uploaded Houses -->
  <h3>Your Houses</h3>
  <table class="user-table" id="uploadedTable">
    <thead>
      <tr>
        <th>Title</th>
        <th>Location</th>
        <th>Price</th>
        <th>Bedrooms</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($houses) === 0): ?>
        <tr><td colspan="6" style="text-align:center;">No houses uploaded yet.</td></tr>
      <?php else: ?>
        <?php foreach ($houses as $house): ?>
          <tr>
            <td><?= htmlspecialchars($house['title']) ?></td>
            <td><?= htmlspecialchars($house['location']) ?></td>
            <td>$<?= number_format($house['price'], 2) ?></td>
            <td><?= (int)$house['bedrooms'] ?></td>
            <td>
              <?php 
                $statusClass = ($house['status'] === 'approved' || $house['status'] === 'available') ? 'active' : 'inactive'; 
              ?>
              <span class="status <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($house['status'])) ?></span>
            </td>
            <td class="actions">
              <i class="fas fa-edit edit" title="Edit"></i>
              <i class="fas fa-trash delete" title="Delete"></i>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Rented Houses -->
  <h3>Rented Houses</h3>
  <table class="user-table" id="rentedTable">
    <thead>
      <tr>
        <th>Title</th>
        <th>Location</th>
        <th>Tenant</th>
        <th>Price</th>
        <th>Start Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rentedHouses)): ?>
        <tr><td colspan="6" style="text-align:center;">No rented houses yet.</td></tr>
      <?php else: ?>
        <?php foreach ($rentedHouses as $rented): ?>
          <tr>
            <td><?= htmlspecialchars($rented['title']) ?></td>
            <td><?= htmlspecialchars($rented['location']) ?></td>
            <td><?= htmlspecialchars($rented['tenant_name']) ?></td>
            <td>$<?= number_format($rented['price'], 2) ?></td>
            <td><?= date('Y-m-d', strtotime($rented['start_date'])) ?></td>
            <td><span class="status inactive">Rented</span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</div>

<script src="./assets/main.js"></script>
<script>
 document.getElementById('openFormBtn').addEventListener('click', function () {
  document.getElementById('houseFormModal').style.display = 'block';
});

document.getElementById('closeFormBtn').addEventListener('click', function () {
  document.getElementById('houseFormModal').style.display = 'none';
});

// Optional: Close modal if clicking outside content
window.addEventListener('click', function (event) {
  const modal = document.getElementById('houseFormModal');
  if (event.target === modal) {
    modal.style.display = 'none';
  }
});

</script>
</body>
</html>
