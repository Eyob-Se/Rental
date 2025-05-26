<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'government') {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch reports joining transactions and users (property manager)
$sql = "
    SELECT r.id AS report_id, r.report_data, r.flagged, r.created_at AS report_date,
           t.id AS transaction_id, t.amount, t.payment_date,
           pm.id AS pm_id, pm.name AS pm_name
    FROM reports r
    JOIN transactions t ON r.transaction_id = t.id
    JOIN users pm ON r.property_manager_id = pm.id
    ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Government Report Page</title>
  <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
  <div class="prop_con">

   <div class="navbar prop_nav">
            <p>Rental.</p>
            <button class="btn" onclick="window.location.href='../auth/logout.php'">Logout</button>
        </div>

    <div class="container">
      <h2>Government Report Dashboard</h2>
      <p>Overview of reports submitted by property managers</p>

      <?php if (empty($reports)): ?>
        <p>No reports found.</p>
      <?php else: ?>
        <table class="user-table">
          <thead>
            <tr>
              <th>Manager Name</th>
              <th>Report ID</th>
              <th>Transaction</th>
              <th>Amount</th>
              <th>Report Date</th>
              <th>Status</th>
              <th>View</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reports as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['pm_name']) ?></td>
                <td>#<?= htmlspecialchars($r['report_id']) ?></td>
                <td>#<?= htmlspecialchars($r['transaction_id']) ?></td>
                <td>$<?= number_format($r['amount'], 2) ?></td>
                <td><?= htmlspecialchars($r['report_date']) ?></td>
                <td><span class="status <?= $r['flagged'] ? 'pending' : 'active' ?>">
                    <?= $r['flagged'] ? 'Flagged' : 'Reviewed' ?>
                </span></td>
                <td><button class="btn viewReportBtn"
                           data-manager="<?= htmlspecialchars($r['pm_name']) ?>"
                           data-report="<?= htmlspecialchars($r['report_data']) ?>"
                           data-date="<?= htmlspecialchars($r['report_date']) ?>"
                           data-amount="<?= number_format($r['amount'], 2) ?>"
                           data-payment="<?= htmlspecialchars($r['payment_date']) ?>"
                           data-transaction="<?= htmlspecialchars($r['transaction_id']) ?>">
                    View
                </button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Modal for Detailed Report -->
    <div class="modal" id="reportDetailModal">
      <div class="modal-content" id="reportContent">
        <span class="close" id="closeDetailModal">&times;</span>
        <h3>Report Details</h3>
        <div class="report-details">
          <p><strong>Manager:</strong> <span id="detailManager"></span></p>
          <p><strong>Submitted On:</strong> <span id="detailDate"></span></p>
          <hr>
          <h4>Transaction</h4>
          <p><strong>Transaction ID:</strong> <span id="detailTransaction"></span></p>
          <p><strong>Amount:</strong> $<span id="detailAmount"></span></p>
          <p><strong>Payment Date:</strong> <span id="detailPayment"></span></p>
          <hr>
          <h4>Report Content</h4>
          <p id="detailReport"></p>
        </div>
        <button class="btn" id="downloadReportBtn">Download PDF</button>
      </div>
    </div>

  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    const modal = document.getElementById("reportDetailModal");
    const closeBtn = document.getElementById("closeDetailModal");
    const viewButtons = document.querySelectorAll(".viewReportBtn");
    const downloadBtn = document.getElementById("downloadReportBtn");

    viewButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        document.getElementById("detailManager").textContent = btn.dataset.manager;
        document.getElementById("detailDate").textContent = btn.dataset.date;
        document.getElementById("detailTransaction").textContent = btn.dataset.transaction;
        document.getElementById("detailAmount").textContent = btn.dataset.amount;
        document.getElementById("detailPayment").textContent = btn.dataset.payment;
        document.getElementById("detailReport").textContent = btn.dataset.report;

        modal.style.display = "flex";
      });
    });

    closeBtn.onclick = () => {
      modal.style.display = "none";
    };

    window.onclick = (e) => {
      if (e.target === modal) modal.style.display = "none";
    };

    downloadBtn.addEventListener("click", () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text("Government Report", 10, 10);

      const reportText = document.querySelector(".report-details").innerText;
      doc.text(reportText, 10, 20);
      doc.save("report.pdf");
    });
  </script>
</body>
</html>
