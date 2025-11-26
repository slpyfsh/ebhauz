<?php
include 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$originalStudId = isset($_POST['originalStudId']) ? trim($_POST['originalStudId']) : '';
$stud_id = isset($_POST['studentId']) ? trim($_POST['studentId']) : '';
$stud_first_name = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$stud_mid_name = isset($_POST['middleName']) ? trim($_POST['middleName']) : '';
$stud_last_name = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$guar_name = isset($_POST['guardianName']) ? trim($_POST['guardianName']) : '';
$guar_cont_no = isset($_POST['guardianContact']) ? trim($_POST['guardianContact']) : '';

if (
    $originalStudId === '' || $stud_id === '' || $stud_first_name === '' ||
    $stud_mid_name === '' || $stud_last_name === '' || $guar_name === '' || $guar_cont_no === ''
) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$originalStudId = $conn->real_escape_string($originalStudId);
$stud_id = $conn->real_escape_string($stud_id);
$stud_first_name = $conn->real_escape_string($stud_first_name);
$stud_mid_name = $conn->real_escape_string($stud_mid_name);
$stud_last_name = $conn->real_escape_string($stud_last_name);
$guar_name = $conn->real_escape_string($guar_name);
$guar_cont_no = $conn->real_escape_string($guar_cont_no);

$sql = "UPDATE tenant_table SET 
            stud_id = '$stud_id',
            stud_first_name = '$stud_first_name',
            stud_mid_name = '$stud_mid_name',
            stud_last_name = '$stud_last_name',
            guar_name = '$guar_name',
            guar_cont_no = '$guar_cont_no'
        WHERE stud_id = '$originalStudId'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Tenant updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
exit;
?>
