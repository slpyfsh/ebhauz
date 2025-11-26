<?php
session_start();
require_once "connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
    header("Location: ../login.php?error=fields");
    exit;
}

// Prepare statement to also fetch user_id and ROLE
$stmt = $conn->prepare("SELECT user_id, username, enc_pass, user_role FROM user_cred WHERE username = ?");
if (!$stmt) {
    header("Location: ../login.php?error=server");
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header("Location: ../login.php?error=username");
    exit;
}

$stmt->bind_result($userId, $db_username, $db_enc_pass, $user_role);
$stmt->fetch();

if (!password_verify($password, $db_enc_pass)) {
    $stmt->close();
    header("Location: ../login.php?error=password");
    exit;
}

// Set Session Variables
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $db_username;
$_SESSION['role'] = $user_role;

session_regenerate_id(true);

// Set Cookie
$cookieName = 'remember_me';
$cookieValue = json_encode([
    'user_id' => $userId,
    'username' => $db_username,
    'role' => $user_role,
]);
setcookie('remember_me', $cookieValue, time() + (86400 * 30), "/", "", false, false);

$stmt->close();
$conn->close();

// --- FIXED REDIRECT LOGIC ---
if ($user_role === 'admin') {
    header("Location: ../admin_bh_list.php");
} elseif ($user_role === 'owner') {
    header("Location: ../home.php");
} else {
    // Default fallback (e.g. for viewers)
    header("Location: ../viewer.php");
}
exit;
?>