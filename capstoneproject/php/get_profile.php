<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch only Owner Personal Info
$sql = "SELECT 
            ov.first_name, ov.mid_name, ov.last_name,
            ot.cont_no, ot.owner_address
        FROM owner_table ot
        JOIN owner_ver ov ON ot.owner_id = ov.owner_id
        WHERE ot.user_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'profile' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Profile not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>