<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'property_manager') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all notifications for this PM
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE receiver_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate payment notifications and general notifications
$payment_notifications = [];
$general_notifications = [];

foreach ($notifications as $note) {
    if (stripos($note['message'], 'payment') !== false || stripos($note['message'], 'receipt') !== false) {
        $payment_notifications[] = $note;
    } else {
        $general_notifications[] = $note;
    }
}

// For each payment notification, get transaction/payment details
$payments_data = []; // key: notification_id, value: payment + house details

foreach ($payment_notifications as $note) {
    $notification_id = $note['id'];
    $tenant_id = $note['sender_id'];

    // Assume the notification message includes house_id or else you store house_id in notification? If not, you'll need a mapping
    // For demo, let's assume notification has house_id in a custom column or parse from message (simplify by assuming a house_id in notification table)
    // If no house_id in notifications, you'll need a better mapping - here I’ll assume notifications table has house_id column:
    
    $house_id = $note['house_id'] ?? null; // Replace or set this accordingly
    
    if (!$house_id) {
        continue; // skip if no house id info
    }

    // Fetch house info
    $stmt = $pdo->prepare("SELECT title, location FROM houses WHERE id = ?");
    $stmt->execute([$house_id]);
    $house = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch latest unverified payment for this tenant & house
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE house_id = ? AND tenant_id = ? AND status = 'unverified' ORDER BY payment_date DESC LIMIT 1");
    $stmt->execute([$house_id, $tenant_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment && $house) {
        $payments_data[$notification_id] = [
            'house' => $house,
            'payment' => $payment,
            'tenant_id' => $tenant_id,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Your Notifications</title>
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
            <li><a href="generate_report.php">Report</a></li>
        </ul>
        <button class="btn" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
        <button class="btn" onclick="window.location.href='dashboard.php'">Back to dashboard</button>

        <div class="notif-section">
            <h2> Payment Verifications</h2>
            <ul class="notif-list">
                <?php if (count($payment_notifications) > 0): ?>
                    <?php foreach ($payment_notifications as $note): ?>
                        <li class="<?= $note['is_read'] ? 'read' : 'unread' ?>">
                            <?= htmlspecialchars($note['message']) ?>
                            <br>
                            <small><em><?= date("M d, Y h:i A", strtotime($note['created_at'])) ?></em></small>

                            <?php if (isset($payments_data[$note['id']])): ?>
                                <button class="btn-verify" 
                                    data-noteid="<?= $note['id'] ?>"
                                    data-house="<?= htmlspecialchars($payments_data[$note['id']]['house']['title']) ?>"
                                    data-location="<?= htmlspecialchars($payments_data[$note['id']]['house']['location']) ?>"
                                    data-amount="<?= number_format($payments_data[$note['id']]['payment']['amount'], 2) ?>"
                                    data-fee="<?= number_format($payments_data[$note['id']]['payment']['fee'] ?? 0, 2) ?>"
                                    data-total="<?= number_format($payments_data[$note['id']]['payment']['amount'] + ($payments_data[$note['id']]['payment']['fee'] ?? 0), 2) ?>"
                                    data-receipt="/Rental/uploads/receipts/<?= htmlspecialchars($payments_data[$note['id']]['payment']['file_path']) ?>"
                                    data-tenantid="<?= $payments_data[$note['id']]['tenant_id'] ?>"
                                    data-paymentid="<?= $payments_data[$note['id']]['payment']['id'] ?>"
                                >Review</button>
                            <?php else: ?>
                                <span style="color: gray; margin-left: 10px;">No unverified payment found</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No payment-related notifications.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="notif-section">
            <h2> General Notifications</h2>
            <ul class="notif-list">
                <?php if (count($general_notifications) > 0): ?>
                    <?php foreach ($general_notifications as $note): ?>
                        <li class="<?= $note['is_read'] ? 'read' : 'unread' ?>">
                            <?= htmlspecialchars($note['message']) ?>
                            <br>
                            <small><em><?= date("M d, Y h:i A", strtotime($note['created_at'])) ?></em></small>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No general notifications.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="verifyModal">
    <div class="modal-content">
        <span class="close-btn" id="modalClose">&times;</span>
        <h3>Payment Verification</h3>
        <p><strong>House:</strong> <span id="modalHouse"></span></p>
        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
        <p><strong>Amount:</strong> $<span id="modalAmount"></span></p>
        <p><strong>Fee:</strong> $<span id="modalFee"></span></p>
        <p><strong>Total:</strong> $<span id="modalTotal" style="color:green;"></span></p>
        <p><strong>Receipt:</strong></p>
        <img src="" alt="Payment Receipt" id="modalReceiptImg" class="receipt-img" />

        <form method="post" action="verify_payment_action.php" id="verifyForm">
            <input type="hidden" name="payment_id" id="paymentIdInput" />
            <input type="hidden" name="notification_id" id="notificationIdInput" />
            <div class="action-buttons">
            <button type="button" onclick="submitAction('verify')">Verify</button>
            <button type="button" onclick="submitAction('reject')">Decline</button>

            </div>
        </form>
    </div>
</div>

<script>
    // Get modal and close button
    const modal = document.getElementById('verifyModal');
    const modalClose = document.getElementById('modalClose');

    // When click close button, hide modal
    modalClose.onclick = () => {
        modal.style.display = "none";
    };

    // When click outside modal content, close modal
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Attach event listeners to all Review buttons
    document.querySelectorAll('.btn-verify').forEach(btn => {
        btn.addEventListener('click', () => {
            // Fill modal with data attributes from button
            document.getElementById('modalHouse').innerText = btn.getAttribute('data-house');
            document.getElementById('modalLocation').innerText = btn.getAttribute('data-location');
            document.getElementById('modalAmount').innerText = btn.getAttribute('data-amount');
            document.getElementById('modalFee').innerText = btn.getAttribute('data-fee');
            document.getElementById('modalTotal').innerText = btn.getAttribute('data-total');
            document.getElementById('modalReceiptImg').src = btn.getAttribute('data-receipt');
            document.getElementById('paymentIdInput').value = btn.getAttribute('data-paymentid');
            document.getElementById('notificationIdInput').value = btn.getAttribute('data-noteid');

            modal.style.display = 'block';
        });
    });
</script>
<script>
function verifyOrRejectPayment(transactionId, action) {
    if (!['verify', 'reject'].includes(action)) return;

    fetch('verify_payment_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `transaction_id=${encodeURIComponent(transactionId)}&action=${encodeURIComponent(action)}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert(data.message);  // Popup with success message

            // After user closes alert, reload or redirect back
            location.reload(); // reloads current page to reflect changes
            // OR
            // window.location.href = 'property_manager_dashboard.php'; // redirect to specific page
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Request failed. Please try again.'));
}
</script>
<script>
function submitAction(action) {
    const transactionId = document.getElementById('paymentIdInput').value;

    if (!transactionId || !['verify', 'reject'].includes(action)) {
        alert("Invalid action or missing transaction.");
        return;
    }

    fetch('verify_payment_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `transaction_id=${encodeURIComponent(transactionId)}&action=${encodeURIComponent(action)}`
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload(); // Refresh the page to show updated status
    })
    .catch(() => {
        alert('Request failed. Please try again.');
    });
}
</script>


</body>
</html>
