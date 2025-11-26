<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

$permit = isset($_GET['permit_no']) ? trim($_GET['permit_no']) : '';

if (!$permit) {
    echo json_encode([]); 
    exit;
}

$permit = $conn->real_escape_string($permit);

// PERMISSION CHECK LOGIC
// 1. Are they logged in as Admin or Owner?
$is_authorized = isset($_SESSION['user_id']) && 
                 ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner');

// 2. If NOT authorized, we must ensure the BH is 'Accredited' (yes) to show it publicly.
$status_check = $is_authorized ? "" : " AND b.accred_status = 'yes'";

// 1. Fetch Basic Details
$sql = "SELECT b.permit_no, b.bh_name, b.bh_address, b.accred_status, 
               o.cont_no, o.owner_address, 
               v.first_name, v.mid_name, v.last_name, v.verif_stat
        FROM bh_table b
        JOIN owner_table o ON b.owner_id = o.owner_id
        JOIN owner_ver v ON o.owner_id = v.owner_id
        WHERE b.permit_no = '$permit' $status_check
        LIMIT 1";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (!$row) { 
    // If no result found (either doesn't exist OR is not accredited and user is guest)
    echo json_encode([]); 
    exit; 
}

// 2. Fetch Policies
$sql2 = "SELECT p.pol_id, COALESCE(p.pol_desc, '') AS pol_name, bs.pol_stat
         FROM pol_table p
         LEFT JOIN bh_status bs ON p.pol_id = bs.pol_id AND bs.bh_id = '$permit'
         ORDER BY p.pol_id ASC";
$res2 = $conn->query($sql2);
$pols = [];
while ($r = $res2->fetch_assoc()) { $pols[] = $r; }

// 3. Fetch Photos
$sql3 = "SELECT photo_path, photo_type FROM bh_photos WHERE permit_no = '$permit'";
$res3 = $conn->query($sql3);
$photos = ['main' => null, 'extras' => []];

while ($p = $res3->fetch_assoc()) {
    if ($p['photo_type'] === 'main') {
        $photos['main'] = $p['photo_path'];
    } else {
        $photos['extras'][] = $p['photo_path'];
    }
}

// Assemble Response
$row['policies'] = $pols;
$row['photos'] = $photos;

echo json_encode($row);
$conn->close();
?>