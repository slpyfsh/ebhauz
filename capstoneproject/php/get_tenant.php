<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');

// 1. SECURITY: Only Owner
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode([]); 
    exit;
}

// 2. LOGIC FIX: Get only THIS owner's tenants
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT b.permit_no FROM bh_table b JOIN owner_table o ON b.owner_id = o.owner_id WHERE o.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$my_bh_id = $row['permit_no'] ?? null;

if(!$my_bh_id) { echo json_encode([]); exit; }

$sql = "SELECT 
          t.stud_id, t.stud_first_name, t.stud_mid_name, t.stud_last_name, 
          t.guar_name, t.guar_cont_no, t.rent_stat, 
          DATE_FORMAT(n.last_notified, '%Y-%m-%d %H:%i:%s') AS last_notified
        FROM tenant_table t
        LEFT JOIN notification_log n ON t.stud_id = n.stud_id
        WHERE t.bh_id = ?"; // FILTER ADDED HERE

$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("s", $my_bh_id);
$stmt2->execute();
$result = $stmt2->get_result();

$tenants = [];
while ($row = $result->fetch_assoc()) {
    $tenants[] = $row;
}

echo json_encode($tenants);
$conn->close();
?>