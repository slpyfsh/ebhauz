<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

// 1. Security Check
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$photoPath = $input['photo_path'] ?? '';
$permitNo  = $input['permit_no'] ?? '';

if (!$photoPath || !$permitNo) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$userId = $_SESSION['user_id'];

// 2. Verify Ownership
// Ensure the permit_no actually belongs to the logged-in user
$stmt = $conn->prepare("SELECT b.permit_no FROM bh_table b JOIN owner_table o ON b.owner_id = o.owner_id WHERE o.user_id = ? AND b.permit_no = ?");
$stmt->bind_param("is", $userId, $permitNo);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Ownership verification failed.']);
    exit;
}
$stmt->close();

// 3. Delete File from Server
$fullPath = '../' . $photoPath; // Adjust path relative to php folder
if (file_exists($fullPath)) {
    unlink($fullPath);
}

// 4. Delete Record from Database
$delStmt = $conn->prepare("DELETE FROM bh_photos WHERE photo_path = ? AND permit_no = ?");
$delStmt->bind_param("ss", $photoPath, $permitNo);

if ($delStmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$delStmt->close();
$conn->close();
?>