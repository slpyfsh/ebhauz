<?php
header('Content-Type: application/json');
require_once "connection.php";

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['permitNumber']) || empty(trim($input['permitNumber']))) {
    echo json_encode(['exists' => false]);
    exit;
}

$permitNumber = trim($input['permitNumber']);

$stmt = $conn->prepare("SELECT COUNT(*) FROM bh_table WHERE permit_no = ?");
if (!$stmt) {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt->bind_param("s", $permitNumber);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
$conn->close();

echo json_encode(['exists' => $count > 0]);