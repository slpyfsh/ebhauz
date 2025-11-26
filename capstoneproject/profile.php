<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/home.css">
    
    <style>
        body {
            padding-top: 80px; 
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .section-title {
            color: #4e73df;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 10px;
            margin-bottom: 20px;
            margin-top: 20px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        .full-width { grid-column: 1 / -1; }

        .btn-container {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn-save {
            background-color: #4e73df;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn-save:hover { background-color: #375ac0; }
    </style>
</head>
<body>

<?php include 'php/navbar.php'; ?>

<div class="profile-container">
    <h2 style="text-align: center; color: #333;">My Profile</h2>
    <p style="text-align: center; color: #666;">Manage your personal information.</p>

    <form id="profileForm">
        
        <div class="section-title">Personal Information</div>
        <div class="form-grid">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" id="firstName" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" id="midName" name="mid_name">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="lastName" name="last_name" required>
            </div>
        </div>

        <div class="section-title">Contact Details</div>
        <div class="form-grid">
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" id="contactNum" name="cont_no" required>
            </div>
            <div class="form-group full-width">
                <label>Owner Address</label>
                <input type="text" id="ownerAddress" name="owner_address" required>
            </div>
        </div>

        <div class="btn-container">
            <button type="submit" class="btn-save">Save Changes</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadProfile();
});

// 1. FETCH DATA
function loadProfile() {
    fetch('php/get_profile.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const p = data.profile;
                document.getElementById('firstName').value = p.first_name;
                document.getElementById('midName').value = p.mid_name;
                document.getElementById('lastName').value = p.last_name;
                document.getElementById('contactNum').value = p.cont_no;
                document.getElementById('ownerAddress').value = p.owner_address;
            } else {
                alert("Failed to load profile: " + data.message);
            }
        })
        .catch(err => console.error(err));
}

// 2. UPDATE DATA
document.getElementById('profileForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    if(!confirm("Are you sure you want to update your information?")) return;

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    fetch('php/update_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            alert("Profile updated successfully!");
        } else {
            alert("Update failed: " + response.message);
        }
    })
    .catch(err => alert("Server error occurred."));
});
</script>

</body>
</html>