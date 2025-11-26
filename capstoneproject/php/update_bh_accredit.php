<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

// SECURITY: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success'=>false, 'message'=>'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if(!$input || !isset($input['permit_no']) || !isset($input['accred_status'])){
    echo json_encode(['success'=>false,'message'=>'Invalid input']); exit;
}

$permit = trim($input['permit_no']);
$status = trim($input['accred_status']);

// MAPPING: UI Status -> Database Value
$dbStatus = $status; // Default (for 'pending')

if($status === 'accredited') {
    $dbStatus = 'yes';
} elseif($status === 'denied') {
    $dbStatus = 'no';
}
// 'pending' remains 'pending' (if your DB allows it, otherwise map to your pending value)

$stmt = $conn->prepare("UPDATE bh_table SET accred_status = ? WHERE permit_no = ?");
$stmt->bind_param("ss", $dbStatus, $permit);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>