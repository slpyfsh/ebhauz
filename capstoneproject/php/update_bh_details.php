<?php
session_start();
header('Content-Type: application/json');
include 'connection.php';

// 1. Security Check
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. We use $_POST now (because of FormData in JS)
$originalPermit = $_POST['original_permit_no'] ?? '';
$newPermit      = trim($_POST['permit_no'] ?? '');
$newName        = trim($_POST['bh_name'] ?? '');
$newAddress     = trim($_POST['bh_address'] ?? '');

// Decode policies (sent as stringified JSON from JS)
$newPolicies = isset($_POST['policies']) ? json_decode($_POST['policies'], true) : [];

$conn->begin_transaction();

try {
    // 3. Status Change Logic (Keep existing logic)
    $stmt = $conn->prepare("SELECT accred_status FROM bh_table WHERE permit_no = ?");
    $stmt->bind_param("s", $originalPermit);
    $stmt->execute();
    $res = $stmt->get_result();
    $currentRow = $res->fetch_assoc();
    $stmt->close();

    if (!$currentRow) throw new Exception("Boarding house not found.");
    
    $currentStatus = $currentRow['accred_status'];
    $shouldRevert = false;

    if ($originalPermit !== $newPermit) $shouldRevert = true;

    // Check policies
    $stmtPol = $conn->prepare("SELECT pol_id, pol_stat FROM bh_status WHERE bh_id = ?");
    $stmtPol->bind_param("s", $originalPermit);
    $stmtPol->execute();
    $resPol = $stmtPol->get_result();
    $oldPolicies = [];
    while ($r = $resPol->fetch_assoc()) { $oldPolicies[$r['pol_id']] = $r['pol_stat']; }
    $stmtPol->close();

    foreach ($newPolicies as $np) {
        if (!isset($oldPolicies[$np['pol_id']]) || $oldPolicies[$np['pol_id']] !== $np['pol_stat']) {
            $shouldRevert = true; break;
        }
    }

    $finalStatus = $shouldRevert ? 'pending' : $currentStatus;

    // 4. Update Main Info
    $updateStmt = $conn->prepare("UPDATE bh_table SET permit_no=?, bh_name=?, bh_address=?, accred_status=? WHERE permit_no=?");
    $updateStmt->bind_param("sssss", $newPermit, $newName, $newAddress, $finalStatus, $originalPermit);
    $updateStmt->execute();
    $updateStmt->close();

    // 5. Update Policies
    $delStmt = $conn->prepare("DELETE FROM bh_status WHERE bh_id = ?");
    $delStmt->bind_param("s", $newPermit);
    $delStmt->execute();
    $delStmt->close();

    $insStmt = $conn->prepare("INSERT INTO bh_status (bh_id, pol_id, pol_stat) VALUES (?, ?, ?)");
    foreach ($newPolicies as $np) {
        $insStmt->bind_param("sis", $newPermit, $np['pol_id'], $np['pol_stat']);
        $insStmt->execute();
    }
    $insStmt->close();

    // ============================================
    // 6. HANDLE FILE UPLOADS
    // ============================================
    $uploadDir = '../uploads/';

    // A. Main Photo (Replace if exists)
    if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
        // Delete old main photo from DB and Folder
        $oldPhotoStmt = $conn->prepare("SELECT photo_path FROM bh_photos WHERE permit_no = ? AND photo_type = 'main'");
        $oldPhotoStmt->bind_param("s", $newPermit);
        $oldPhotoStmt->execute();
        $oldRes = $oldPhotoStmt->get_result();
        if ($old = $oldRes->fetch_assoc()) {
            if (file_exists('../' . $old['photo_path'])) unlink('../' . $old['photo_path']);
            $conn->query("DELETE FROM bh_photos WHERE permit_no = '$newPermit' AND photo_type = 'main'");
        }
        $oldPhotoStmt->close();

        // Upload New
        $ext = pathinfo($_FILES['main_photo']['name'], PATHINFO_EXTENSION);
        $filename = 'main_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['main_photo']['tmp_name'], $uploadDir . $filename);
        $dbPath = 'uploads/' . $filename;

        $insPhoto = $conn->prepare("INSERT INTO bh_photos (permit_no, photo_path, photo_type) VALUES (?, ?, 'main')");
        $insPhoto->bind_param("ss", $newPermit, $dbPath);
        $insPhoto->execute();
        $insPhoto->close();
    }

    // B. Additional Photos (Limit 5 total)
    if (isset($_FILES['extra_photos'])) {
        // Count existing extras
        $cntStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM bh_photos WHERE permit_no = ? AND photo_type = 'extra'");
        $cntStmt->bind_param("s", $newPermit);
        $cntStmt->execute();
        $resCnt = $cntStmt->get_result()->fetch_assoc();
        $currentCount = $resCnt['cnt'];
        $cntStmt->close();

        $totalFiles = count($_FILES['extra_photos']['name']);
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($currentCount >= 5) break; // Stop if limit reached
            
            if ($_FILES['extra_photos']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['extra_photos']['name'][$i], PATHINFO_EXTENSION);
                $filename = 'extra_' . time() . '_' . uniqid() . $i . '.' . $ext;
                move_uploaded_file($_FILES['extra_photos']['tmp_name'][$i], $uploadDir . $filename);
                $dbPath = 'uploads/' . $filename;

                $insExtra = $conn->prepare("INSERT INTO bh_photos (permit_no, photo_path, photo_type) VALUES (?, ?, 'extra')");
                $insExtra->bind_param("ss", $newPermit, $dbPath);
                $insExtra->execute();
                $insExtra->close();
                $currentCount++;
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>