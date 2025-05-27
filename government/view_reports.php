<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

// Fetch owners with their houses
$stmt = $pdo->prepare("
    SELECT u.id AS owner_id, u.name AS owner_name, h.*
    FROM users u
    JOIN houses h ON u.id = h.owner_id
    WHERE u.role = 'owner' AND h.is_rented = 1
    ORDER BY u.name, h.title
");

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group houses by owner
$owners = [];
foreach ($rows as $row) {
    $ownerId = $row['owner_id'];
    if (!isset($owners[$ownerId])) {
        $owners[$ownerId] = [
            'owner_name' => $row['owner_name'],
            'houses' => [],
        ];
    }
    $owners[$ownerId]['houses'][] = $row;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Government View Reports</title>
    <link rel="stylesheet" href="../assets/style1.css" />
    <link rel="stylesheet" href="../assets/fonts/all.css" />
    <style>
    body {

        background-color: #2b2d42;
    }

    .container {
        max-width: 1200px;
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        color: #333;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        /* reduce top margin */
        padding: 20px;
        border-radius: 5px;
        width: 90%;
        /* was 80%, now 90% */
        max-width: 1000px;
        /* ensure it doesn’t get too wide on huge screens */
        overflow-x: auto;
        /* allow scroll if content is too wide */
    }

    .modal-actions textarea {
        width: 100%;
        height: 80px;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .container {
        margin-top: 3rem;
        background-color: #e0d0c1;
    }
    </style>
</head>

<body>
    <div class="navbar prop_nav">
        <p>Rental.</p>
        <button class="btn" type="button" onclick="window.location.href='../auth/logout.php'">Logout</button>
    </div>

    <div class="container">
        <h2 style="color: #2b2d42;padding-bottom:1rem;">Owner Reports</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Rented Houses</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($owners as $ownerId => $data): ?>
                <tr>
                    <td><?= htmlspecialchars($data['owner_name']) ?></td>
                    <td><?= count($data['houses']) ?></td>
                    <td>
                        <button class="btn open-modal" data-owner="<?= $ownerId ?>">View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modals -->
    <?php foreach ($owners as $ownerId => $data): ?>
    <div id="modal-<?= $ownerId ?>" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" data-owner="<?= $ownerId ?>">&times;</span>
            <h3><?= htmlspecialchars($data['owner_name']) ?>'s Rented Properties</h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Bedrooms</th>
                        <th>Price</th>
                        <th>Area</th>
                        <th>Tax</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['houses'] as $house): ?>
                    <tr>
                        <td><?= htmlspecialchars($house['title']) ?></td>
                        <td><?= $house['bedrooms'] ?></td>
                        <td>$<?= $house['price'] ?></td>
                        <td><?= $house['area'] ?> sq ft</td>
                        <td>$<?= number_format($house['price'] * 0.15, 2) ?> (15%)</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="POST" action="send_notice.php" class="modal-actions">
                <input type="hidden" name="owner_id" value="<?= $ownerId ?>">
                <textarea name="message" placeholder="Write a notice..." required></textarea>
                <button type="submit" class="btn">Send Notice</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
    document.querySelectorAll('.open-modal').forEach(btn => {
        btn.onclick = () => {
            document.getElementById(`modal-${btn.dataset.owner}`).style.display = 'block';
        };
    });

    document.querySelectorAll('.close').forEach(span => {
        span.onclick = () => {
            document.getElementById(`modal-${span.dataset.owner}`).style.display = 'none';
        };
    });

    window.onclick = e => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    };
    </script>
</body>

</html>