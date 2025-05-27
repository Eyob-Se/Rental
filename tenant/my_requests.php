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

// Fetch all rental requests for this tenant
$stmt = $pdo->prepare("SELECT rr.*, h.title, h.price, h.location FROM rental_requests rr JOIN houses h ON rr.house_id = h.id WHERE rr.tenant_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

// Fetch all related transactions for this tenant
$stmtTx = $pdo->prepare("SELECT house_id, status FROM transactions WHERE tenant_id = ?");
$stmtTx->execute([$_SESSION['user_id']]);
$transactions = $stmtTx->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Rent Requests</title>
    <link rel="stylesheet" href="../assets/style1.css" />
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

        <div class="container">
            <h3>My Rent Requests</h3>
            <table class="user-table" id="requestsTable">
                <thead>
                    <tr>
                        <th>House</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $req): ?>
                    <?php
                            $houseId = $req['house_id'];
                            $txStatus = isset($transactions[$houseId]) ? $transactions[$houseId]['status'] : null;
                        ?>
                    <tr>
                        <td><?= htmlspecialchars($req['title']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($req['status'])) ?></td>
                        <td><?= date("F j, Y", strtotime($req['created_at'] ?? 'now')) ?></td>
                        <td>
                            <?php if ($req['status'] === 'approved'): ?>
                            <?php if (!$txStatus || $txStatus === 'rejected'): ?>
                            <button class="btn open-modal-btn" data-house="<?= htmlspecialchars($req['title']) ?>"
                                data-rent="<?= htmlspecialchars($req['price']) ?>"
                                data-location="<?= htmlspecialchars($req['location']) ?>"
                                data-houseid="<?= htmlspecialchars($req['house_id']) ?>"
                                data-requestid="<?= htmlspecialchars($req['id']) ?>">
                                Pay
                            </button>
                            <?php if ($txStatus === 'rejected'): ?>
                            <p style="color: red; margin-top: 6px;">Your payment was rejected. Please upload a valid
                                receipt.</p>
                            <?php endif; ?>
                            <?php elseif ($txStatus === 'unverified'): ?>
                            <button class="btn" disabled>Pending Verification</button>
                            <p style="color: orange; margin-top: 6px;">Waiting for property manager to verify your
                                payment.</p>
                            <?php elseif ($txStatus === 'verified'): ?>
                            <button class="btn" style="background-color: gray;" disabled>Payment Verified</button>
                            <p style="color: green; margin-top: 6px;">Your payment has been verified. Thank you!</p>
                            <?php endif; ?>
                            <?php else: ?>
                            <span style="color: gray;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No rental requests found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br />
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>

    <!-- Modal Container -->
    <div class="modal" id="paymentModal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Payment Details</h3>
            <p id="payment-info"></p>

            <form id="paymentForm" action="upload_receipt.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="house_id" id="modalHouseId" value="">
                <input type="hidden" name="request_id" id="modalRequestId" value="">
                <input type="hidden" name="amount" id="modalAmount" value="">

                <label>Upload Payment Receipt:</label><br>
                <input type="file" name="receipt_file" accept=".jpg,.png,.jpeg,.pdf" required><br><br>
                <button type="submit" class="btn">Submit Receipt</button>
            </form>
        </div>
    </div>

    <script>
    const modal = document.getElementById('paymentModal');
    const paymentInfo = document.getElementById('payment-info');
    const modalHouseId = document.getElementById('modalHouseId');
    const modalRequestId = document.getElementById('modalRequestId');
    const modalAmount = document.getElementById('modalAmount');
    const closeBtn = document.querySelector('.close-btn');

    // Open modal when pay button clicked
    document.querySelectorAll('.open-modal-btn').forEach(button => {
        button.addEventListener('click', () => {
            const house = button.getAttribute('data-house');
            const rent = parseFloat(button.getAttribute('data-rent'));
            const location = button.getAttribute('data-location');
            const houseId = button.getAttribute('data-houseid');
            const requestId = button.getAttribute('data-requestid');

            const fee = rent * 0.10;
            const total = rent + fee;

            paymentInfo.innerHTML = `
                <strong>House:</strong> ${house}<br>
                <strong>Location:</strong> ${location}<br>
                <strong>Rent:</strong> $${rent.toFixed(2)}<br>
                <strong>Platform Fee (10%):</strong> $${fee.toFixed(2)}<br>
                <strong>Total Amount:</strong> <span style="color: green;"><strong>$${total.toFixed(2)}</strong></span><br><br>
                <strong>Payment Options:</strong><br>
                <ul style="padding-left: 18px;">
                    <li><strong>CBE:</strong><br>
                        Account Name: Rental Ethiopia<br>
                        Account Number: 1000057693739
                    </li><br>
                    <li><strong>Telebirr:</strong><br>
                        Account Name: Rental Pay<br>
                        Phone Number: +251912345678
                    </li>
                </ul>
            `;

            modalHouseId.value = houseId;
            modalRequestId.value = requestId;
            modalAmount.value = total.toFixed(2);

            modal.style.display = 'flex';
        });
    });

    // Close modal on X click
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Close modal if clicking outside modal content
    window.addEventListener('click', e => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    </script>

</body>

</html>