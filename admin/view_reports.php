<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $report_id = $_POST['report_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    if ($action === 'flag_user' && $user_id) {
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$user_id]);
    } elseif ($action === 'delete_user' && $user_id) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    } elseif ($action === 'delete_report' && $report_id) {
        $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->execute([$report_id]);
    }

    header("Location: view_reports.php");
    exit;
}

// Fetch all reports with correct joins
$stmt = $pdo->prepare("
    SELECT 
        r.id AS report_id,
        r.report_data,
        r.created_at AS report_date,
        u.id AS user_id,
        u.name AS user_name,
        u.role AS user_role,
        u.status AS user_status,
        pm.name AS pm_name
    FROM reports r
    JOIN transactions t ON r.transaction_id = t.id
    JOIN users u ON t.tenant_id = u.id
    JOIN users pm ON r.property_manager_id = pm.id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Reports</title>
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
        <li><a href="view_reports.php" class="active">Reports</a></li>
      </ul>
      <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
      <h2>Reports Submitted by Property Managers</h2>
      <p>Monitor and act on reports submitted by PMs</p>

      <?php if (!$reports): ?>
        <p>No reports found.</p>
      <?php else: ?>
        <div class="top-bar">
          <input type="text" id="searchInput" placeholder="🔍 Search reports...">
        </div>

        <div style="overflow-x:auto;">
          <table class="user-table" id="reportTable">
            <thead>
              <tr>
                <th>Report ID</th>
                <th>Text</th>
                <th>Date</th>
                <th>Reported User</th>
                <th>Role</th>
                <th>Status</th>
                <th>PM Name</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reports as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['report_id']) ?></td>
                  <td><?= htmlspecialchars($r['report_data']) ?></td>
                  <td><?= htmlspecialchars($r['report_date']) ?></td>
                  <td><?= htmlspecialchars($r['user_name']) ?></td>
                  <td><?= htmlspecialchars($r['user_role']) ?></td>
                  <td>
                    <span class="status <?= $r['user_status'] === 'inactive' ? 'inactive' : 'active' ?>">
                      <?= htmlspecialchars($r['user_status']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($r['pm_name']) ?></td>
                  <td class="actions">
                    <?php if ($r['user_status'] !== 'inactive'): ?>
                      <form method="POST" action="view_reports.php" style="display:inline;">
                        <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                        <input type="hidden" name="user_id" value="<?= $r['user_id'] ?>">
                        <input type="hidden" name="action" value="flag_user">
                        <button class="btn" type="submit">Deactivate</button>
                      </form>
                    <?php endif; ?>
                    <form method="POST" action="view_reports.php" style="display:inline;">
                      <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                      <input type="hidden" name="user_id" value="<?= $r['user_id'] ?>">
                      <input type="hidden" name="action" value="delete_user">
                      <button class="btn" type="submit" onclick="return confirm('Delete this user?')">Delete User</button>
                    </form>
                    <form method="POST" action="view_reports.php" style="display:inline;">
                      <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                      <input type="hidden" name="action" value="delete_report">
                      <button class="btn" type="submit" onclick="return confirm('Delete this report?')">Delete Report</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
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

  <script src="../assets/main.js"></script>
  <script>
    // Search filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
      const searchValue = this.value.toLowerCase();
      const rows = document.querySelectorAll('#reportTable tbody tr');
      rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(searchValue) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
