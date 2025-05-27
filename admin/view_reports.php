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

    if ($action === 'mark_read' && $report_id) {
        $stmt = $pdo->prepare("UPDATE reports_for_admin SET is_read = 1 WHERE id = ?");
        $stmt->execute([$report_id]);
    }

    header("Location: view_reports.php");
    exit;
}
$show_read = isset($_GET['show_read']) && $_GET['show_read'] == 1;

// Fetch reports based on read status
if ($show_read) {
    $stmt = $pdo->prepare("
        SELECT r.id AS report_id, r.subject, r.description, r.created_at, r.is_read,
               pm.name AS pm_name
        FROM reports_for_admin r
        JOIN users pm ON r.property_manager_id = pm.id
        WHERE r.is_read = 1
        ORDER BY r.created_at DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT r.id AS report_id, r.subject, r.description, r.created_at, r.is_read,
               pm.name AS pm_name
        FROM reports_for_admin r
        JOIN users pm ON r.property_manager_id = pm.id
        WHERE r.is_read = 0
        ORDER BY r.created_at DESC
    ");
}
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
        <li><a href="manage_pm.php">Prop Mng</a></li>
        <li><a href="manage_users.php">User Mng</a></li>
        <li><a href="view_reports.php" class="active">Reports</a></li>
      </ul>
      <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
      <h2>Reports Submitted by Property Managers</h2>
      <p>Monitor and act on reports submitted by PMs</p>

<div class="top-bar">
  <input type="text" id="searchInput" placeholder="🔍 Search reports...">
  <a href="view_reports.php?show_read=<?= $show_read ? 0 : 1 ?>" class="btn" style="margin-left:10px;">
    <?= $show_read ? 'Show Unread Reports' : 'Show Read Reports' ?>
  </a>
</div>

<?php if (!$reports): ?>
  <p>No reports found.</p>
<?php else: ?>
  <div style="overflow-x:auto;">
    <table class="user-table" id="reportTable">
      <thead>
        <tr>
          <th>Report ID</th>
          <th>Subject</th>
          <th>Description</th>
          <th>Date</th>
          <th>Property Manager</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['report_id']) ?></td>
            <td><?= htmlspecialchars($r['subject']) ?></td>
            <td><?= htmlspecialchars($r['description']) ?></td>
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td><?= htmlspecialchars($r['pm_name']) ?></td>
            <td class="actions">
              <?php if ($r['is_read'] == 0): ?>
                <form method="POST" action="view_reports.php" style="display:inline;">
                  <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                  <input type="hidden" name="action" value="mark_read">
                  <button class="btn" type="submit" onclick="return confirm('Mark this report as read?')">Mark as Read</button>
                </form>
              <?php else: ?>
                <span style="color: gray;">✔️ Read</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

    </div>

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
