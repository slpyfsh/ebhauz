<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registration.php');
    exit;
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

$requiredFields = [
    'username', 'password', 'confirmPassword',
    'firstName', 'midName', 'lastName', 'address',
    'contactNumber', 'permitNumber', 'bhName', 'bhAddress'
];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = ucfirst($field) . " is required.";
        header('Location: ../registration.php');
        exit;
    }
}

if ($_POST['password'] !== $_POST['confirmPassword']) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: ../registration.php');
    exit;
}

if (!preg_match('/^09\d{9}$/', $_POST['contactNumber'])) {
    $_SESSION['error'] = "Invalid contact number format.";
    header('Location: ../registration.php');
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])) {
    $_SESSION['error'] = "Username can only contain letters, numbers, and underscores.";
    header('Location: ../registration.php');
    exit;
}

$username = sanitize($conn, $_POST['username']);
$sql = "SELECT user_id FROM user_cred WHERE username = '$username' LIMIT 1";
if ($conn->query($sql)->num_rows > 0) {
    $_SESSION['error'] = "Username already exists.";
    header('Location: ../registration.php');
    exit;
}

function capitalizeWordsPHP($str) {
    return ucwords(strtolower(trim($str)));
}

$firstName  = capitalizeWordsPHP($_POST['firstName']);
$midName    = capitalizeWordsPHP($_POST['midName']);
$lastName   = capitalizeWordsPHP($_POST['lastName']);
$address    = capitalizeWordsPHP($_POST['address']);
$bhAddress  = capitalizeWordsPHP($_POST['bhAddress']);
$bhName     = capitalizeWordsPHP($_POST['bhName']);

$contactNumber = sanitize($conn, $_POST['contactNumber']);
$permitNumber  = sanitize($conn, $_POST['permitNumber']);

$hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO user_cred (user_role, username, enc_pass) VALUES ('owner', ?, ?)");
$stmt->bind_param("ss", $username, $hashedPassword);
if (!$stmt->execute()) {
    $_SESSION['error'] = "Error creating user: " . $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: ../registration.php');
    exit;
}
$userId = $stmt->insert_id;
$stmt->close();

$stmt2 = $conn->prepare("INSERT INTO owner_ver (first_name, mid_name, last_name, verif_stat) VALUES (?, ?, ?, 'pending')");
$stmt2->bind_param("sss", $firstName, $midName, $lastName);
if (!$stmt2->execute()) {
    $_SESSION['error'] = "Error creating owner verification: " . $stmt2->error;
    $stmt2->close();
    $conn->close();
    header('Location: ../registration.php');
    exit;
}
$ownerId = $stmt2->insert_id;
$stmt2->close();

$stmt3 = $conn->prepare("INSERT INTO owner_table (user_id, owner_id, cont_no, owner_address) VALUES (?, ?, ?, ?)");
$stmt3->bind_param("iiss", $userId, $ownerId, $contactNumber, $address);
if (!$stmt3->execute()) {
    $_SESSION['error'] = "Error creating owner info: " . $stmt3->error;
    $stmt3->close();
    $conn->close();
    header('Location: ../registration.php');
    exit;
}
$stmt3->close();

$stmt4 = $conn->prepare("INSERT INTO bh_table (permit_no, owner_id, bh_name, bh_address, accred_status) VALUES (?, ?, ?, ?, 'no')");
$stmt4->bind_param("siss", $permitNumber, $ownerId, $bhName, $bhAddress);
if (!$stmt4->execute()) {
    $_SESSION['error'] = "Error creating boarding house: " . $stmt4->error;
    $stmt4->close();
    $conn->close();
    header('Location: ../registration.php');
    exit;
}
$stmt4->close();

$sqlPolicies = "SELECT pol_id FROM pol_table";
$resultPolicies = $conn->query($sqlPolicies);
if ($resultPolicies && $resultPolicies->num_rows > 0) {
    while ($row = $resultPolicies->fetch_assoc()) {
        $polId = (int)$row['pol_id'];
        $policyKey = "policy_{$polId}";

        if (!isset($_POST[$policyKey])) {
            $_SESSION['error'] = "Policy responses incomplete.";
            header('Location: ../registration.php');
            exit;
        }

        $polStat = $_POST[$policyKey] === 'yes' ? 'yes' : 'no';

        $stmt5 = $conn->prepare("INSERT INTO bh_status (bh_id, pol_id, pol_stat) VALUES (?, ?, ?)");
        $stmt5->bind_param("sis", $permitNumber, $polId, $polStat);
        if (!$stmt5->execute()) {
            $_SESSION['error'] = "Error saving policy status: " . $stmt5->error;
            $stmt5->close();
            $conn->close();
            header('Location: ../registration.php');
            exit;
        }
        $stmt5->close();
    }
}

$conn->close();

header('Location: ../login.php?registered=1');
exit;
?>
