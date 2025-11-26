<?php
include 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$studId = isset($_POST['studId']) ? trim($_POST['studId']) : '';

if ($studId === '') {
    echo json_encode(['success' => false, 'message' => 'Missing studId']);
    exit;
}

$studIdEsc = $conn->real_escape_string($studId);

$sql = "DELETE FROM tenant_table WHERE stud_id = '$studIdEsc'";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Tenant deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tenant not found or already deleted']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
exit;
?>
