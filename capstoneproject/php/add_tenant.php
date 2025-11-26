<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');

// 1. SECURITY: Only Owner
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. LOGIC FIX: Find the Real Boarding House ID
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT b.permit_no FROM bh_table b JOIN owner_table o ON b.owner_id = o.owner_id WHERE o.user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No boarding house found for your account.']);
        exit;
    }
    
    $row = $res->fetch_assoc();
    $real_bh_id = $row['permit_no']; // Use this instead of 'admin'
    $stmt->close();

    // 3. Process Input
    $stud_id = $conn->real_escape_string($_POST['studentId'] ?? '');
    $stud_first_name = $conn->real_escape_string($_POST['firstName'] ?? '');
    $stud_mid_name = $conn->real_escape_string($_POST['middleName'] ?? '');
    $stud_last_name = $conn->real_escape_string($_POST['lastName'] ?? '');
    $guar_name = $conn->real_escape_string($_POST['guardianName'] ?? '');
    $guar_cont_no = $conn->real_escape_string($_POST['guardianContact'] ?? '');

    if (empty($stud_id) || empty($stud_first_name) || empty($stud_last_name)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
        exit;
    }

    // 4. Insert with Real BH ID
    $sql = "INSERT INTO tenant_table 
            (bh_id, stud_id, stud_first_name, stud_mid_name, stud_last_name, guar_name, guar_cont_no, rent_stat)
            VALUES ('$real_bh_id', '$stud_id', '$stud_first_name', '$stud_mid_name', '$stud_last_name', '$guar_name', '$guar_cont_no', 'no')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Tenant added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}
?>