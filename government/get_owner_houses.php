<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

$owner_id = intval($_GET['owner_id'] ?? 0);
$exclude_id = intval($_GET['exclude_id'] ?? 0);

$stmt = $pdo->prepare("SELECT title, price FROM houses WHERE owner_id = ? AND id != ? AND is_rented = 1");
$stmt->execute([$owner_id, $exclude_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_dump($result);
exit;