<?php
session_start();
require_once '../config/db.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'];
    $role = $_POST['role']; // Receive role from form

    if (($action === 'activate' || $action === 'deactivate') && in_array($role, ['tenant', 'owner'])) {
        $new_status = $action === 'activate' ? 'active' : 'inactive';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = ?");
        $stmt->execute([$new_status, $user_id, $role]);
    } elseif ($action === 'delete' && in_array($role, ['tenant', 'owner'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = ?");
        $stmt->execute([$user_id, $role]);
    }

    header("Location: manage_users.php");
    exit;
}

// Fetch all tenants and owners
$stmt = $pdo->prepare("SELECT * FROM users WHERE role IN ('tenant', 'owner')");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Tenants</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/style1.css" />
  <link rel="stylesheet" href="../assets/fonts/all.css">
</head>
<body>
  <div class="prop_con">
    <div class="navbar prop_nav">
      <p>Rental.</p>
      <ul>
        <li><a href="manage_pm.php">Prop Mng</a></li>
        <li><a href="manage_users.php">User Mng</a></li>
        <li><a href="view_reports.php">Reports</a></li>
      </ul>
      <button class="btn" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
      <h2>Manage Tenant Accounts</h2>
      <p>Administer tenants in the system</p>

<div class="top-bar">
  <input type="text" id="searchInput" placeholder="🔍 Search users...">

  <select id="roleFilter">
    <option value="all">All Roles</option>
    <option value="tenant">Tenant</option>
    <option value="owner">Owner</option>
  </select>
</div>

      <table class="user-table" id="userTable">
        <thead>
<tr>
  <th>ID</th><th>NAME</th><th>EMAIL</th><th>ROLE</th><th>STATUS</th><th>ACTIONS</th>
</tr>
        </thead>
        <tbody>
<?php foreach ($users as $user): ?>
  <tr>
    <td><?= htmlspecialchars($user['id']) ?></td>
    <td><?= htmlspecialchars($user['name']) ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td><?= ucfirst($user['role']) ?></td>
    <td>
      <span class="status <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
        <?= ucfirst($user['status']) ?>
      </span>
    </td>
    <td class="actions">
      <form method="POST" action="manage_users.php" style="display:inline;">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <input type="hidden" name="role" value="<?= $user['role'] ?>">
        <input type="hidden" name="action" value="<?= $user['status'] === 'active' ? 'deactivate' : 'activate' ?>">
        <button class="btn" type="submit">
          <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
        </button>
      </form>

      <form method="POST" action="manage_users.php" style="display:inline;" onsubmit="return confirm('Delete this user?');">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <input type="hidden" name="role" value="<?= $user['role'] ?>">
        <input type="hidden" name="action" value="delete">
        <button class="btn" type="submit">Delete</button>
      </form>
    </td>
  </tr>
<?php endforeach; ?>
        </tbody>
      </table>
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
  const searchInput = document.getElementById('searchInput');
  const roleFilter = document.getElementById('roleFilter');
  const userTableRows = document.querySelectorAll('#userTable tbody tr');

  function filterTable() {
    const searchValue = searchInput.value.toLowerCase();
    const selectedRole = roleFilter.value;

    userTableRows.forEach(row => {
      const name = row.cells[1].textContent.toLowerCase();
      const email = row.cells[2].textContent.toLowerCase();
      const role = row.cells[3].textContent.toLowerCase();

      const matchesSearch = name.includes(searchValue) || email.includes(searchValue);
      const matchesRole = selectedRole === 'all' || role === selectedRole;

      row.style.display = matchesSearch && matchesRole ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', filterTable);
  roleFilter.addEventListener('change', filterTable);
</script>

</body>
</html>
