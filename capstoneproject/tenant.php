<?php 
session_start();
// 1. Security Check (Role)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

// 2. Connect to DB
include 'php/connection.php'; 

// 3. STRICT ACCREDITATION CHECK
$userId = $_SESSION['user_id'];

// Fetch status for this owner's house
$stmt = $conn->prepare("
    SELECT b.accred_status 
    FROM bh_table b 
    JOIN owner_table o ON b.owner_id = o.owner_id 
    WHERE o.user_id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$dbStatus = strtolower($row['accred_status'] ?? 'no');

$stmt->close();

// 4. Redirect if NOT 'yes' (Accredited)
if ($dbStatus !== 'yes') {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html> 
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tenants</title>
  <link rel="stylesheet" href="css/tenant.css" />
  
  <style>
      body { padding-top: 80px; }
  </style>
</head>
<body>

  <?php include 'php/navbar.php'; ?>

  <main>
    <div class="top-buttons">
      <button id="addTenantBtn">Add Tenant</button>
      <button id="toggleEditBtn">Edit</button>
    </div>

    <div id="tenantListContainer">
    </div>
  </main>

  <div id="popupForm">
    <div class="form-container">
      <h2 id="formTitle">Add Tenant</h2>
      <label>Student ID:</label>
      <input type="text" id="studentId" />
      <small class="error" id="studentIdError"></small>
      <label>First Name:</label>
      <input type="text" id="firstName" />
      <small class="error" id="firstNameError"></small>
      <label>Middle Name:</label>
      <input type="text" id="middleName" />
      <small class="error" id="middleNameError"></small>
      <label>Last Name:</label>
      <input type="text" id="lastName" />
      <small class="error" id="lastNameError"></small>
      <label>Guardian Name:</label>
      <input type="text" id="guardianName" />
      <small class="error" id="guardianNameError"></small>
      <label>Guardian Contact Number:</label>
      <input type="text" id="guardianContact" />
      <small class="error" id="guardianContactError"></small>
      <div class="form-buttons">
        <button id="cancelBtn" type="button">Cancel</button>
        <button id="submitBtn" type="button">Add</button>
      </div>
    </div>
  </div>

  <div id="alertPopup" class="alert-popup"></div>

  <div id="confirmModal" class="confirm-modal" aria-hidden="true">
    <div class="confirm-container">
      <p id="confirmMessage">Are you sure?</p>
      <div class="confirm-buttons">
        <button id="confirmCancelBtn" type="button">Cancel</button>
        <button id="confirmOkBtn" type="button">Delete</button>
      </div>
    </div>
  </div>

  <script src="js/tenant.js"></script>
</body>
</html>