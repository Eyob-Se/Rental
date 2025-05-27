<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

if ($action === 'create') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // new hidden input from form
    $status = 'active';

    if (in_array($role, ['property_manager', 'government'])) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $status]);
    }

    } elseif ($action === 'update') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
      $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role IN ('property_manager', 'government')");


        $stmt->execute([$name, $email, $id]);

    } elseif ($action === 'toggle_status') {
        $id = $_POST['id'];
        $new_status = $_POST['new_status'];
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role IN ('property_manager', 'government')");

        $stmt->execute([$new_status, $id]);

    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role IN ('property_manager', 'government')");

        $stmt->execute([$id]);
    }

    header("Location: manage_pm.php");
    exit;
}

// Fetch all property managers
$stmt = $pdo->prepare("SELECT * FROM users WHERE role IN ('property_manager', 'government')");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Property Managers</title>
  <link rel="stylesheet" href="../assets/style1.css" />
  <link rel="stylesheet" href="../assets/fonts/all.css">
</head>
<body>
  <div class="prop_con">
    <div class="navbar prop_nav">
      <p>Rental.</p>
      <ul>
        <li><a href="manage_pm.php">Home</a></li>
        <li><a href="manage_users.php">User Mng</a></li>
        <li><a href="view_reports.php">Reports</a></li>
      </ul>
      <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
      <h2>Manage Property Managers</h2>
      <p>Administer property manager accounts</p>

      <div class="top-bar">
        <input type="text" id="searchInput" placeholder="🔍 Search...">
        <button class="btn" id="openModalBtn">+ Add Property Manager</button>
        <button class="btn" id="openGovModalBtn">+ Add Government User</button>

      </div>

      <table class="user-table" id="userTable">
        <thead>
          <tr>
            <th>NAME</th>
            <th>EMAIL</th>
           <th>STATUS</th>
           <th>ROLE</th>
            <th>ACTIONS</th>

          </tr>
        </thead>
        <tbody>
<?php foreach ($users as $pm): ?>
  <tr>
    <td><strong><?= htmlspecialchars($pm['name']) ?></strong></td>
    <td><?= htmlspecialchars($pm['email']) ?></td>
    <td>
      <span class="status <?= $pm['status'] === 'active' ? 'active' : 'inactive' ?>">
        <?= ucfirst($pm['status']) ?>
      </span>
    </td>
    <td><?= ucfirst($pm['role']) ?></td> 
    <td class="actions">
      <form method="POST" action="manage_pm.php" style="display:inline;">
        <input type="hidden" name="id" value="<?= $pm['id'] ?>">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="new_status" value="<?= $pm['status'] === 'active' ? 'inactive' : 'active' ?>">
        <button class="btn" type="submit"><?= $pm['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
      </form>
      <form method="POST" action="manage_pm.php" style="display:inline;">
        <input type="hidden" name="id" value="<?= $pm['id'] ?>">
        <input type="hidden" name="action" value="delete">
        <button class="btn" type="submit" onclick="return confirm('Delete this manager?')">Delete</button>
      </form>
    </td>
  </tr>
<?php endforeach; ?>
</tbody>

      </table>
    </div>

    <!-- Modal -->
    <div class="modal" id="userModal">
      <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h3>Add Property Manager</h3>
        <form method="POST" action="manage_pm.php">
          <input type="hidden" name="action" value="create">
          <label>Name</label>
          <input type="text" name="name" required>
          <label>Email</label>
          <input type="email" name="email" required>
          <label>Password</label>
          <input type="password" name="password" required>
          <button type="submit">Add</button>
        </form>
      </div>  
    </div>

    <!-- Government User Modal -->
<div class="modal" id="govModal">
  <div class="modal-content">
    <span class="close" id="closeGovModal">&times;</span>
    <h3>Add Government User</h3>
    <form method="POST" action="manage_pm.php">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="role" value="government">
      <label>Name</label>
      <input type="text" name="name" required>
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Add</button>
    </form>
  </div>  
</div>


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

  <script src="../assets/main.js"></script>
  <script>
  // Property Manager Modal
  const modal = document.getElementById('userModal');
  const openBtn = document.getElementById('openModalBtn');
  const closeBtn = document.getElementById('closeModal');

  openBtn.onclick = () => modal.style.display = 'block';
  closeBtn.onclick = () => modal.style.display = 'none';

  // Government Modal
  const govModal = document.getElementById('govModal');
  const openGovBtn = document.getElementById('openGovModalBtn');
  const closeGovBtn = document.getElementById('closeGovModal');

  openGovBtn.onclick = () => govModal.style.display = 'block';
  closeGovBtn.onclick = () => govModal.style.display = 'none';

  window.onclick = (e) => {
    if (e.target == modal) modal.style.display = 'none';
    if (e.target == govModal) govModal.style.display = 'none';
  };
  </script>
  <script>
  document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#userTable tbody tr');

    rows.forEach(row => {
      const name = row.cells[0].textContent.toLowerCase();
      const email = row.cells[1].textContent.toLowerCase();
      const role = row.cells[3].textContent.toLowerCase();
      
      if (name.includes(filter) || email.includes(filter) || role.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
</script>

</body>
</html>
