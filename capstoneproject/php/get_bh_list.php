<?php
header('Content-Type: application/json');
include 'connection.php';
$sql = "SELECT b.permit_no, b.bh_name, b.bh_address, b.accred_status, o.cont_no, o.owner_address, v.first_name, v.mid_name, v.last_name, v.verif_stat, o.owner_id
FROM bh_table b
JOIN owner_table o ON b.owner_id = o.owner_id
JOIN owner_ver v ON o.owner_id = v.owner_id
ORDER BY b.permit_no ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($row = $res->fetch_assoc()){
    $out[] = $row;
}
$stmt->close();
echo json_encode($out);
$conn->close();