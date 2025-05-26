<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
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
    <button class="btn logout" onclick="window.location.href='../auth/logout.php'">Logout</button>
  </div>

  <div class="container">
    <h2>Welcome, Owner</h2>
    <p>Manage your property listings</p>

    <div class="top-bar">
      <input type="text" id="filterInput" placeholder="🔍 Filter by location, price, etc.">
      <button class="btn" id="openFormBtn">+ Upload New House</button>
    </div>

    <!-- House Upload Form Modal -->
    <div class="modal" id="houseFormModal" style="display:none;">
      <div class="modal-content">
        <span class="close" id="closeFormBtn">&times;</span>
        <h3>Upload House</h3>

        <form id="houseForm" method="POST" enctype="multipart/form-data" action="./upload_house_process.php">
          <label>Title</label>
          <input type="text" name="title" placeholder="House Title" required />

          <label>Location</label>
          <input type="text" name="location" placeholder="City, Area" required />

          <label>Price (USD)</label>
          <input type="number" name="price" step="0.01" placeholder="Monthly Rent" required />

          <label>Bedrooms</label>
          <input type="number" name="bedrooms" min="0" placeholder="e.g., 2" required />

          <label>Bathrooms</label>
          <input type="number" name="bathrooms" min="0" placeholder="e.g., 1" required />

          <label>Area (sq ft)</label>
          <input type="number" name="area" step="0.1" placeholder="e.g., 1200" required />

          <label>Description</label>
          <textarea name="description" placeholder="Brief Description" rows="3" required></textarea>

          <label>Upload Image</label>
          <input type="file" name="image" accept="image/*" required />

          <button type="submit">Submit</button>
        </form>
      </div>
    </div>

    <!-- Uploaded Houses Table -->
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
        <!-- TODO: Populate with PHP pulling houses for this owner -->
      </tbody>
    </table>

    <!-- Rented Houses Table -->
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
        <!-- TODO: Populate rented houses for this owner -->
      </tbody>
    </table>

  </div>

</div>

<script src="../assets/main.js"></script>
<script>
  // Open modal
  document.getElementById("openFormBtn").addEventListener("click", function () {
    document.getElementById("houseFormModal").style.display = "block";
  });

  // Close modal
  document.getElementById("closeFormBtn").addEventListener("click", function () {
    document.getElementById("houseFormModal").style.display = "none";
  });

  // Close modal clicking outside content
  window.onclick = function(event) {
    const modal = document.getElementById("houseFormModal");
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };

  // Submit form with AJAX
  document.getElementById("houseForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch(form.action, {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        form.reset();
        document.getElementById("houseFormModal").style.display = "none";
        // TODO: Reload houses table dynamically if needed
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert("Upload failed. Please try again.");
    });
  });
</script>
</body>
</html>
