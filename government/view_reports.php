<?php
require_once '../config/db.php';

// Fetch owners with their houses
$stmt = $pdo->prepare("
    SELECT u.id AS owner_id, u.name AS owner_name, h.*
    FROM users u
    JOIN houses h ON u.id = h.owner_id
    WHERE u.role = 'owner'
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
    <link rel="stylesheet" href="../assets/style1.css">
</head>
<body>
<div class="container">
    <h2>Owner Reports</h2>
    <table class="user-table">
        <thead>
            <tr>
                <th>Owner</th>
                <th>House Count</th>
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
        <h3><?= htmlspecialchars($data['owner_name']) ?>'s Properties</h3>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Title</th><th>Bedrooms</th><th>Price</th><th>Area</th><th>Tax</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['houses'] as $house): ?>
                <tr>
                    <td><?= htmlspecialchars($house['title']) ?></td>
                    <td><?= $house['bedrooms'] ?></td>
                    <td>$<?= $house['price'] ?></td>
                    <td><?= $house['area'] ?> sq ft</td>
                    <td>$<?= number_format($house['price'] * 0.015, 2) ?> (1.5%)</td>
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
