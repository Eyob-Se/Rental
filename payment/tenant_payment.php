<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$tenant_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT la.id AS lease_id, la.monthly_rent, h.title 
                       FROM lease_agreements la 
                       JOIN houses h ON la.house_id = h.id 
                       WHERE la.tenant_id = ? AND la.status = 'active'");
$stmt->execute([$tenant_id]);
$leases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Payment</title>
    <link rel="stylesheet" href="../assets/style1.css" />
</head>
<body>
<div class="container">
    <h2>Make Payment</h2>
    <form method="POST" action="process_payment.php">
        <label for="lease">Select Lease</label>
        <select name="lease_id" required>
            <?php foreach ($leases as $lease): ?>
                <option value="<?= $lease['lease_id'] ?>">
                    <?= htmlspecialchars($lease['title']) ?> - $<?= $lease['monthly_rent'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="method">Payment Method</label>
        <select name="method" required>
            <option value="chapa">Chapa</option>
        </select>

        <label for="amount">Amount</label>
        <input type="number" name="amount" step="0.01" required id="amountInput" value="<?= $leases[0]['monthly_rent'] ?? 0 ?>">

        <label for="service_fee">Service Fee (2%)</label>
        <input type="text" id="feeOutput" readonly>

        <label for="total">Total to Pay</label>
        <input type="text" id="totalOutput" readonly>

        <button type="submit" class="btn">Proceed to Pay</button>
    </form>
</div>

<script>
    const amountInput = document.getElementById('amountInput');
    const feeOutput = document.getElementById('feeOutput');
    const totalOutput = document.getElementById('totalOutput');

    function updateTotals() {
        const amount = parseFloat(amountInput.value || 0);
        const fee = +(amount * 0.02).toFixed(2);
        feeOutput.value = `$${fee}`;
        totalOutput.value = `$${(amount + fee).toFixed(2)}`;
    }

    amountInput.addEventListener('input', updateTotals);
    window.onload = updateTotals;
</script>
</body>
</html>
