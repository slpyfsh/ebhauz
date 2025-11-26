<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$userId = $_SESSION['user_id'];

// 1. Get Owner ID
$stmt = $conn->prepare("SELECT owner_id FROM owner_table WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$ownerId = $row['owner_id'] ?? null;
$stmt->close();

if (!$ownerId) {
    echo json_encode(['success' => false, 'message' => 'Owner ID not found']);
    exit;
}

// 2. Start Transaction
$conn->begin_transaction();

try {
    // A. Update Names (owner_ver)
    $stmt1 = $conn->prepare("UPDATE owner_ver SET first_name=?, mid_name=?, last_name=? WHERE owner_id=?");
    $fname = trim($input['first_name']);
    $mname = trim($input['mid_name']);
    $lname = trim($input['last_name']);
    $stmt1->bind_param("sssi", $fname, $mname, $lname, $ownerId);
    $stmt1->execute();
    $stmt1->close();

    // B. Update Contact/Address (owner_table)
    $stmt2 = $conn->prepare("UPDATE owner_table SET cont_no=?, owner_address=? WHERE owner_id=?");
    $cont = trim($input['cont_no']);
    $addr = trim($input['owner_address']);
    $stmt2->bind_param("ssi", $cont, $addr, $ownerId);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
}

$conn->close();
?>