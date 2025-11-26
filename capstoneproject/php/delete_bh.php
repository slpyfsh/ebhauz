<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

// SECURITY: Only Admin can delete
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success'=>false, 'message'=>'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid method']);
    exit;
}

$permit_no = isset($_POST['permit_no']) ? trim($_POST['permit_no']) : '';
if ($permit_no === '') {
    echo json_encode(['success'=>false,'message'=>'permit_no required']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM bh_table WHERE permit_no = ?");
$stmt->bind_param("s", $permit_no);

if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>