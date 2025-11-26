<?php
// 1. Start Session
session_start();
include 'connection.php';
header('Content-Type: application/json');

// 2. Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$studId = $data['studId'] ?? '';
$contact = $data['contact'] ?? '';

if (!$studId || !$contact) {
    echo json_encode(['success' => false, 'message' => 'Missing data.']);
    exit;
}

// 3. Clean Contact Number (TextBee usually needs +63 format)
$contact = preg_replace('/[^0-9]/', '', $contact); 
// If number starts with 09, convert to +639
if (substr($contact, 0, 2) === '09') {
    $contact = '+63' . substr($contact, 1);
}
// If it just needs the +, ensure it's there
if (substr($contact, 0, 1) !== '+') {
    $contact = '+' . $contact;
}

// 4. Cooldown Check
$conn->query("CREATE TABLE IF NOT EXISTS notification_log (
  stud_id VARCHAR(50) PRIMARY KEY,
  last_notified DATETIME
)");

$stmt = $conn->prepare("SELECT last_notified FROM notification_log WHERE stud_id = ?");
$stmt->bind_param("s", $studId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if ($row) {
    $last = strtotime($row['last_notified']);
    $now = time();
    $days = ($now - $last) / (60 * 60 * 24);
    if ($days < 30) {
        echo json_encode([
            'success' => false,
            'message' => 'Guardian notified recently (30-day cooldown).',
            'last_notified' => $row['last_notified']
        ]);
        exit;
    }
}

// ==========================================
// 5. SEND SMS VIA TEXTBEE API
// ==========================================

// --- YOUR TEXTBEE CREDENTIALS ---
$apiKey   = '2e9dc204-0d24-4d0d-adad-c8d3145e1735'; 
$deviceId = '69272050eb5540025b1fb873'; 

$message = "Good day! This is a reminder regarding your child's boarding house rent status. Please settle your balance if unpaid.";

$url = "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}/send-sms";

$payload = [
    'recipients' => [$contact], // TextBee expects an array
    'message'    => $message
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-api-key: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// 6. Handle Response
// TextBee returns 201 or 200 for success
if ($httpCode >= 200 && $httpCode < 300) {
    $nowStr = date('Y-m-d H:i:s');
    
    $stmt2 = $conn->prepare("INSERT INTO notification_log (stud_id, last_notified) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_notified = ?");
    $stmt2->bind_param("sss", $studId, $nowStr, $nowStr);
    $stmt2->execute();

    echo json_encode([
        'success' => true,
        'message' => 'SMS sent successfully via TextBee.',
        'last_notified' => $nowStr
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send SMS via TextBee.',
        'debug_error' => $curlErr,
        'debug_response' => $response,
        'http_code' => $httpCode
    ]);
}

$conn->close();
?>