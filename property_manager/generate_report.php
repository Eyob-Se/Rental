<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

$reportMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $pm_id = $_SESSION['user_id'];

    if ($subject && $description) {
        $stmt = $pdo->prepare("INSERT INTO reports (property_manager_id, subject, description) VALUES (?, ?, ?)");
        $stmt->execute([$pm_id, $subject, $description]);
        $reportMessage = "✅ Report submitted successfully.";
    } else {
        $reportMessage = "❌ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Report</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
    <div class="prop_con">
        <div class="navbar prop_nav">
            <p>Rental.</p>
            <ul>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="review_request.php">Requests</a></li>
                <li><a href="review_house.php">Approvals</a></li>
                <li><a href="lease-agreements.php">Agreements</a></li>
                <li><a href="generate_report.php">Reports</a></li>
            </ul>
            <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
        </div>

        <div class="container">
            <h2>Generate Report</h2>

            <?php if ($reportMessage): ?>
                <p style="color: <?= str_starts_with($reportMessage, '✅') ? 'green' : 'red' ?>;">
                    <?= htmlspecialchars($reportMessage) ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="report-form">
                <label for="subject">Subject:</label><br>
                <input type="text" name="subject" id="subject" required><br><br>

                <label for="description">Description:</label><br>
                <textarea name="description" id="description" rows="5" required></textarea><br><br>

                <button class="btn" type="submit">Submit Report</button>
            </form>
        </div>
    </div>
</body>
</html>
