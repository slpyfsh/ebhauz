<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'loggedIn' => isset($_SESSION['user_id']),
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
    'role' => isset($_SESSION['role']) ? $_SESSION['role'] : null
]);
exit;
?>
