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
if(!$input || !isset($input['owner_id']) || !isset($input['verif_stat'])){
    echo json_encode(['success'=>false,'message'=>'Invalid input']); exit;
}
$owner = (int)$input['owner_id'];
$verif = trim($input['verif_stat']);

$stmt = $conn->prepare("UPDATE owner_ver SET verif_stat = ? WHERE owner_id = ?");
$stmt->bind_param("si", $verif, $owner);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>