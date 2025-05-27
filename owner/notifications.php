<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user is owner
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
if ($stmt->fetchColumn() !== 'owner') {
    die("Access denied.");
}

// Fetch rental requests forwarded to this owner
$stmt = $pdo->prepare("
    SELECT rr.id AS request_id, rr.message, rr.status, rr.created_at,
           rr.pm_id,
           u.name AS sender_name,
           h.id AS house_id, h.title AS house_title, h.bedrooms, h.bathrooms, h.area, h.price, h.image_path
    FROM rental_requests rr
    JOIN users u ON rr.tenant_id = u.id
    JOIN houses h ON rr.house_id = h.id
    WHERE h.owner_id = ? AND rr.status = 'forwarded'
    ORDER BY rr.created_at DESC
");
$stmt->execute([$user_id]);
$rentalRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch general notifications sent to this owner
$stmt = $pdo->prepare("
    SELECT n.*, u.name AS sender_name
    FROM notifications n
    JOIN users u ON n.sender_id = u.id
    WHERE n.receiver_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$user_id]);
$generalNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Owner Notifications</title>
    <link rel="stylesheet" href="../assets/style1.css" />
    <link rel="stylesheet" href="../assets/fonts/all.css" />
</head>

<body>
    <div class="prop_con">
        <!-- Navigation -->
        <div class="navbar prop_nav">
            <p>Rental.</p>
            <ul>
                <li><a href="notifications.php" class="active">Notifications</a></li>
                <li><a href="dashboard.php">Houses</a></li>
            </ul>
            <button class="btn" onclick="window.location.href='../auth/logout.php'">Logout</button>
        </div>

        <div class="container">
            <div class="top-bar">
                <input type="text" id="notificationFilter" placeholder="🔍 Filter notifications" />
            </div>

            <!-- Rental Requests -->
            <section>
                <h3>Rental Requests</h3>
                <table class="user-table" id="notificationTable">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Received</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rentalRequests)): ?>
                        <tr>
                            <td colspan="4">No notifications.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rentalRequests as $note): ?>
                        <tr>
                            <td><?= htmlspecialchars($note['sender_name']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($note['created_at'])) ?></td>
                            <td>Rental request received for house: <?= htmlspecialchars($note['house_title']) ?></td>
                            <td>
                                <button class="btn open-modal" data-id="<?= $note['request_id'] ?>"
                                    data-sender="<?= htmlspecialchars($note['sender_name']) ?>"
                                    data-sender-id="<?= $note['pm_id'] ?>"
                                    data-message="Rental request for <?= htmlspecialchars($note['house_title']) ?>"
                                    data-house="<?= htmlspecialchars($note['house_title']) ?>"
                                    data-house-id="<?= $note['house_id'] ?>" data-bedrooms="<?= $note['bedrooms'] ?>"
                                    data-bathrooms="<?= $note['bathrooms'] ?>" data-area="<?= $note['area'] ?>"
                                    data-price="<?= $note['price'] ?>"
                                    data-image="<?= htmlspecialchars($note['image_path']) ?>"
                                    data-status="<?= $note['status'] ?>">
                                    Respond
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- General Notifications -->
            <section>
                <h3>General Notifications</h3>
                <table class="user-table" id="generalNotificationTable">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Received</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($generalNotifications)): ?>
                        <tr>
                            <td colspan="3">No notifications.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($generalNotifications as $note): ?>
                        <tr>
                            <td><?= htmlspecialchars($note['sender_name']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($note['created_at'])) ?></td>
                            <td><?= htmlspecialchars($note['message']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <!-- Modal -->
    <div id="actionModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Respond to Rental Request</h3>
            <p><strong>Tenant:</strong> <span id="modalSender"></span></p>
            <p><strong>Message:</strong> <span id="modalMessage"></span></p>
            <p><strong>House:</strong> <span id="modalHouse"></span></p>
            <p><strong>Bedrooms:</strong> <span id="modalBedrooms"></span></p>
            <p><strong>Bathrooms:</strong> <span id="modalBathrooms"></span></p>
            <p><strong>Area:</strong> <span id="modalArea"></span> sq ft</p>
            <p><strong>Price:</strong> $<span id="modalPrice"></span></p>
            <img id="modalImage" src="" alt="House Image" style="max-width: 100%; border-radius: 8px;" />

            <form method="POST" action="handle_request.php" class="modal-actions">
                <input type="hidden" name="house_id" id="modalHouseId" value="">
                <input type="hidden" name="request_id" id="modalNotificationId" value="">
                <input type="hidden" name="sender_id" id="modalSenderId" value="">
                <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                <button type="submit" name="action" value="decline" class="btn reject">Decline</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
                // Modal logic
                document.querySelectorAll('.open-modal').forEach(button => {
                        button.addEventListener('click', () => {
                                if (button.dataset.status === 'notice') {
                                    // This is a government notice, open govNoticeModal instead
                                    document.getElementById('govNoticeSender').textContent = button.dataset
                                        .sender;
                                    document.getElementById('govNoticeMessage').textContent = button.dataset
                                        .message;
                                    document.getElementById('govNoticeModal').style.display = 'block';

                                    // Hide rental request modal if open (just in case)
                                    document.getElementById('actionModal').style.display = 'none';
                                } else {
                                    // Rental request modal
                                    document.getElementById('modalSender').textContent = button.dataset.sender;
                                    document.getElementById('modalMessage').textContent = button.dataset
                                        .message;
                                    document.getElementById('modalHouse').textContent = button.dataset.house;
                                    document.getElementById('modalBedrooms').textContent = button.dataset
                                        .bedrooms;
                                    document.getElementById('modalBathrooms').textContent = button.dataset
                                        .bathrooms;
                                    document.getElementById('modalArea').textContent = button.dataset.area;
                                    document.getElementById('modalPrice').textContent = button.dataset.price;
                                    document.getElementById('modalImage').src = "../uploads/house_images/" +
                                        button.dataset.image;

                                    document.querySelector('.modal-actions').style.display = button.dataset
                                        .status === 'forwarded' ? 'flex' : 'none';
                                    document.getElementById('actionModal').style.display = 'block';
                                });
                        });

                    // Close buttons for rental requests modal
                    document.querySelectorAll('.close').forEach(closeBtn => {
                        closeBtn.onclick = () => {
                            document.getElementById('actionModal').style.display = 'none';
                            document.getElementById('govNoticeModal').style.display = 'none';
                        };
                    });

                    // Close modals when clicking outside modal content
                    window.onclick = event => {
                        if (event.target === document.getElementById('actionModal')) {
                            document.getElementById('actionModal').style.display = 'none';
                        }
                    };

                    // Filter logic
                    const filterInput = document.getElementById('notificationFilter'); filterInput.addEventListener(
                        'input', () => {
                            const filter = filterInput.value.toLowerCase();
                            ['notificationTable', 'generalNotificationTable'].forEach(tableId => {
                                const rows = document.querySelectorAll(`#${tableId} tbody tr`);
                                rows.forEach(row => {
                                    const rowText = row.textContent.toLowerCase();
                                    row.style.display = rowText.includes(filter) ? '' : 'none';
                                });
                            });
                        });
                });
    </script>
</body>

</html>