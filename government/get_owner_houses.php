<?php
require_once '../config/db.php';

$owner_id = intval($_GET['owner_id'] ?? 0);
$exclude_id = intval($_GET['exclude_id'] ?? 0);

$stmt = $pdo->prepare("SELECT title, price FROM houses WHERE owner_id = ? AND id != ?");
$stmt->execute([$owner_id, $exclude_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
