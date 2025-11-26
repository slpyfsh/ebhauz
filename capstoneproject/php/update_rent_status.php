<?php
include 'connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['studId']) || !isset($data['rentStat'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$studId = $conn->real_escape_string($data['studId']);
$rentStat = ($data['rentStat'] === 'yes') ? 'yes' : 'no';

$sql = "UPDATE tenant_table SET rent_stat = '$rentStat' WHERE stud_id = '$studId'";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Rent status updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
?>