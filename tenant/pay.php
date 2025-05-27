<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$tenant_id = $_SESSION['user_id'];

try {
    // Get approved rental requests
    $stmt = $pdo->prepare("SELECT rr.house_id, h.title, h.price 
        FROM rental_requests rr
        JOIN houses h ON rr.house_id = h.id
        WHERE rr.tenant_id = ? AND rr.status = 'approved'");
    $stmt->execute([$tenant_id]);
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching approved houses: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Make Payment</title>
    <style>
    body {
        font-family: Arial;
        padding: 20px;
    }

    form {
        max-width: 400px;
        margin: auto;
    }

    label,
    select,
    input {
        display: block;
        width: 100%;
        margin-bottom: 15px;
    }

    button {
        padding: 10px 20px;
        background: #333;
        color: white;
        border: none;
    }
    </style>
</head>

<body>
    <h2>Make a Payment</h2>

    <?php if (empty($houses)): ?>
    <p>No approved rental requests found.</p>
    <?php else: ?>
    <form method="post" action="../transaction/confirm.php">
        <label for="house_id">Select House:</label>
        <select name="house_id" id="house_id" required>
            <?php foreach ($houses as $house): ?>
            <option value="<?= $house['house_id'] ?>">
                <?= htmlspecialchars($house['title']) ?> — $<?= number_format($house['price'], 2) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <label for="tax">Tax (e.g. 5% of price):</label>
        <input type="number" name="tax" id="tax" step="0.01" required>

        <input type="hidden" name="amount" id="amount" value="">

        <button type="submit">Confirm Payment</button>
    </form>

    <script>
    const houses = <?= json_encode($houses) ?>;
    const houseSelect = document.getElementById("house_id");
    const amountInput = document.getElementById("amount");

    function updateAmount() {
        const selected = houseSelect.value;
        const house = houses.find(h => h.house_id == selected);
        if (house) {
            amountInput.value = house.price;
        }
    }

    houseSelect.addEventListener("change", updateAmount);
    updateAmount(); // init
    </script>
    <?php endif; ?>
</body>

</html>