<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

// 1. SECURITY: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// 2. Validate Input
$permit_no = isset($_POST['permit_no']) ? trim($_POST['permit_no']) : '';
$new_pass = isset($_POST['new_password']) ? $_POST['new_password'] : '';

if (empty($permit_no) || empty($new_pass)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// 3. Find the user_id linked to this permit_no
// Link: bh_table (permit) -> owner_table (owner_id) -> user_cred (user_id)
$stmt = $conn->prepare("
    SELECT o.user_id 
    FROM bh_table b 
    JOIN owner_table o ON b.owner_id = o.owner_id 
    WHERE b.permit_no = ? 
    LIMIT 1
");
$stmt->bind_param("s", $permit_no);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Owner not found for this permit']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $res->fetch_assoc();
$user_id = $row['user_id'];
$stmt->close();

// 4. Hash the new password
$hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

// 5. Update the password in user_cred table
$stmt2 = $conn->prepare("UPDATE user_cred SET enc_pass = ? WHERE user_id = ?");
$stmt2->bind_param("si", $hashed_password, $user_id);

if ($stmt2->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt2->error]);
}

$stmt2->close();
$conn->close();
?>