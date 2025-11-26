<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');

// 1. Security Check
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$ownerId = $_SESSION['user_id'];

// 2. Get Actual Owner ID from owner_table
$stmt = $conn->prepare("SELECT owner_id FROM owner_table WHERE user_id = ?");
$stmt->bind_param("i", $ownerId);
$stmt->execute();
$stmt->bind_result($ownerIdFromDb);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Owner profile not found.']);
    exit;
}
$stmt->close();

// 3. Validate Inputs
$permitNumber = trim($_POST['permitNumber'] ?? '');
$bhName       = trim($_POST['bhName'] ?? '');
$bhAddress    = trim($_POST['bhAddress'] ?? '');

if ($permitNumber === '' || $bhName === '' || $bhAddress === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
    exit;
}

$conn->begin_transaction();

try {
    // 4. Insert Boarding House
    $stmt2 = $conn->prepare("INSERT INTO bh_table (permit_no, owner_id, bh_name, bh_address, accred_status) VALUES (?, ?, ?, ?, 'no')");
    $stmt2->bind_param("siss", $permitNumber, $ownerIdFromDb, $bhName, $bhAddress);
    
    if (!$stmt2->execute()) {
        throw new Exception("Permit number might already exist.");
    }
    $stmt2->close();

    // 5. Insert Policies (Loop through available policies in DB)
    $sqlPol = "SELECT pol_id FROM pol_table";
    $resPol = $conn->query($sqlPol);
    
    if ($resPol) {
        $stmt3 = $conn->prepare("INSERT INTO bh_status (bh_id, pol_id, pol_stat) VALUES (?, ?, ?)");
        while ($row = $resPol->fetch_assoc()) {
            $polId = $row['pol_id'];
            $inputName = "policy_" . $polId;
            
            // Check if user selected 'yes' or 'no' in the form
            $status = isset($_POST[$inputName]) ? $_POST[$inputName] : 'no';
            
            $stmt3->bind_param("sis", $permitNumber, $polId, $status);
            $stmt3->execute();
        }
        $stmt3->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Boarding house added successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>