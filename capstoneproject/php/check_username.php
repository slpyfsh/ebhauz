<?php
header('Content-Type: application/json');
include 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'])) {
    echo json_encode(['exists' => false]);
    exit;
}

$username = $conn->real_escape_string(trim($data['username']));

$sql = "SELECT user_id FROM user_cred WHERE username = '$username' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}

$conn->close();
?>
