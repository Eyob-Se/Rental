<?php
session_start();

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
 header("Location: ../index.php");
exit();

}
?>