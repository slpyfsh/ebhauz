<?php
session_start();
include_once 'php/connection.php'; 
include 'php/navbar.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get Owner ID
$stmt = $conn->prepare("SELECT owner_id FROM owner_table WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($ownerId);
$stmt->fetch();
$stmt->close();

// Fetch BHs
$query = "SELECT b.permit_no, b.bh_name, b.accred_status, 
          (SELECT photo_path FROM bh_photos WHERE permit_no = b.permit_no AND photo_type = 'main' LIMIT 1) as main_photo
          FROM bh_table b 
          WHERE b.owner_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ownerId);
$stmt->execute();
$result = $stmt->get_result();

$boardingHouses = [];
while ($row = $result->fetch_assoc()) {
    $boardingHouses[] = $row;
}
$stmt->close();
// Note: We keep $conn open to fetch policies in the HTML below
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Boarding Houses</title>
<link rel="stylesheet" href="css/form.css" />
<link rel="stylesheet" href="css/home.css" />

<style>
    /* (Your existing styles remain the same) */
    .bh-card { position: relative !important; overflow: hidden; cursor: pointer; transition: transform 0.2s, opacity 0.2s; }
    .bh-card.not-accredited { opacity: 0.75; background-color: #f1f1f1; border: 2px dashed #ccc; }
    .card-image { height: 140px; background-color: #e0e0e0; background-size: cover; background-position: center; background-repeat: no-repeat; }
    .card-name { padding: 15px 12px; text-align: center; font-weight: bold; color: white; background: #4e73df; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; display: block; }
    .card-menu { position: absolute !important; top: 8px !important; right: 8px !important; z-index: 100 !important; font-size: 24px; font-weight: bold; color: #333; background: rgba(255, 255, 255, 0.8); padding: 0 8px; border-radius: 4px; line-height: 24px; }
    .card-status-badge { position: absolute; top: 8px; right: 40px; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; color: white; font-weight: 700; text-transform: uppercase; z-index: 90; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .status-yes { background: #1cc88a; } .status-no { background: #e74a3b; } .status-pending { background: #f6c23e; color: #444; }
    
    .details-modal, .popup-form {
        position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: white; padding: 25px; width: 90%; max-width: 600px;
        border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 11000; display: none; max-height: 90vh; overflow-y: auto;
    }
    /* Fix popup form specific style since it shares class */
    .popup-form { z-index: 12000; }

    .edit-input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; }
    .policy-item { background: #f8f9fa; padding: 10px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #eee; }
    .radio-group { display: flex; gap: 15px; margin-top: 5px; }
    
    /* Lightbox */
    .lightbox-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 20000; display: none; align-items: center; justify-content: center; }
    .lightbox-content { max-width: 90%; max-height: 90%; border-radius: 4px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
    .lightbox-close { position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; cursor: pointer; font-weight: bold; }
    
    .photo-preview { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
    .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    .photo-wrapper { position: relative; width: 70px; height: 70px; border: 1px solid #ddd; border-radius: 4px; background: white; }
    .photo-wrapper img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; cursor: zoom-in; }
    .btn-delete-photo { position: absolute; top: -6px; right: -6px; background: #e74a3b; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; }
    .file-input-list { display: flex; flex-direction: column; gap: 8px; }
    
    .btn-cancel { background:#e74a3b; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;}
    .btn-submit { background:#4e73df; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;}
</style>
</head>
<body>

<div class="form-container" style="margin-top: 120px;">
    <h2>Your Boarding House(s)</h2>
    <div class="cards-container">
        <?php foreach ($boardingHouses as $bh): 
            $status = strtolower($bh['accred_status']);
            $cardClass = ($status === 'yes') ? '' : 'not-accredited';
            $badgeColor = 'status-pending'; $badgeText = 'Pending';
            if($status === 'yes') { $badgeColor = 'status-yes'; $badgeText = 'Accredited'; }
            if($status === 'no')  { $badgeColor = 'status-no';  $badgeText = 'Denied'; }
            $bgImage = $bh['main_photo'] ? $bh['main_photo'] : ''; 
        ?>
            <div class="bh-card bh-link <?= $cardClass ?>" tabindex="0" 
                 data-permit="<?= htmlspecialchars($bh['permit_no']) ?>"
                 data-status="<?= htmlspecialchars($status) ?>">
                <div class="card-menu" title="Edit Details">&#8942;</div>
                <div class="card-image" style="background-image: url('<?= $bgImage ?>');"></div>
                <div class="card-name"><?= htmlspecialchars($bh['bh_name']) ?></div>
                <div class="card-status-badge <?= $badgeColor ?>"><?= $badgeText ?></div>
            </div>
        <?php endforeach; ?>
        <div class="bh-card add-new" id="addNewCard" tabindex="0">+</div>
    </div>
</div>

<div class="popup-overlay" id="popupOverlay" style="display:none;"></div>

<div class="popup-form" id="popupForm" style="display:none;">
    <h3 style="color:#4e73df; border-bottom:1px solid #eee; padding-bottom:10px;">Add Boarding House</h3>
    <form id="addBhForm">
        <div class="form-group">
            <label>Permit Number</label>
            <input type="text" name="permitNumber" class="edit-input" required />
        </div>
        <div class="form-group">
            <label>Boarding House Name</label>
            <input type="text" name="bhName" class="edit-input" required />
        </div>
        <div class="form-group">
            <label>Boarding House Address</label>
            <input type="text" name="bhAddress" class="edit-input" required />
        </div>

        <h4 style="margin: 15px 0 10px; color:#4e73df;">Policies Check</h4>
        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 5px; border-radius: 4px;">
            <?php
                // REUSE CONNECTION TO FETCH POLICIES
                $polSql = "SELECT pol_id, pol_desc FROM pol_table ORDER BY pol_id ASC";
                $polRes = $conn->query($polSql);
                if ($polRes) {
                    while ($pol = $polRes->fetch_assoc()) {
                        $pid = $pol['pol_id'];
                        echo '<div class="policy-item">';
                        echo '<div><small>' . htmlspecialchars($pol['pol_desc']) . '</small></div>';
                        echo '<div class="radio-group">';
                        echo '<label><input type="radio" name="policy_'.$pid.'" value="yes" required> Followed</label>';
                        echo '<label><input type="radio" name="policy_'.$pid.'" value="no" required> Not Followed</label>';
                        echo '</div></div>';
                    }
                }
            ?>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
            <button type="submit" class="btn-submit">Add Boarding House</button>
        </div>
    </form>
</div>

<div class="details-modal" id="detailsModal">
    <h3>Edit Boarding House</h3>
    <form id="editBhForm" enctype="multipart/form-data">
        <input type="hidden" id="originalPermit" name="original_permit_no">
        
        <label><strong>Name:</strong></label>
        <input type="text" id="editName" name="bh_name" class="edit-input" required>

        <label><strong>Address:</strong></label>
        <input type="text" id="editAddress" name="bh_address" class="edit-input" required>

        <label><strong>Permit Number:</strong> <small style="color:red;">(Reverts status to Pending)</small></label>
        <input type="text" id="editPermit" name="permit_no" class="edit-input" required>

        <hr>
        <div class="photo-section-title">Main Photo (Background):</div>
        <div id="currentMainPhoto" class="existing-photos-container"></div>
        <input type="file" id="mainPhotoInput" name="main_photo" accept="image/*" class="edit-input">

        <div class="photo-section-title">Additional Photos (Max 5):</div>
        <div id="existingExtrasContainer" class="existing-photos-container"></div>
        <div id="extraInputsContainer" class="file-input-list"></div>
        
        <hr>
        <h4 style="margin: 15px 0 10px; color:#4e73df;">Policies <small style="font-size:0.7em; color:red;">(Changes revert status to Pending)</small></h4>
        <div id="editPoliciesList"></div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn-cancel" id="closeDetailsBtn">Cancel</button>
            <button type="submit" class="btn-submit">Save Changes</button>
        </div>
    </form>
</div>

<div id="lightbox" class="lightbox-modal" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img id="lightboxImg" class="lightbox-content" src="">
</div>

<script>
    // --- 1. ADD NEW BH LOGIC ---
    document.getElementById('addNewCard').addEventListener('click', () => {
        document.getElementById('popupOverlay').style.display = 'block';
        document.getElementById('popupForm').style.display = 'block';
    });

    // Handle Form Submission (AJAX)
    document.getElementById('addBhForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

        fetch('php/add_boarding_house.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Boarding House added successfully!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => alert("Server error occurred."));
    });

    // --- 2. EDIT & VIEW LOGIC ---
    document.querySelectorAll(".bh-link").forEach(card => {
        card.addEventListener("click", (e) => {
            const permit = card.dataset.permit;
            const status = card.dataset.status;

            if(e.target.classList.contains('card-menu')) {
                e.stopPropagation(); 
                openEditModal(permit);
            } else {
                if (status !== 'yes') {
                    alert("Access Denied: Boarding house is not Accredited.");
                    return; 
                }
                window.location.href = `tenant.php?permit=${permit}`;
            }
        });
    });

    function openEditModal(permit) {
        document.getElementById('mainPhotoInput').value = '';
        document.getElementById('popupOverlay').style.display = 'block';
        document.getElementById('detailsModal').style.display = 'block';
        document.getElementById('editPoliciesList').innerHTML = 'Loading...';

        fetch(`php/get_bh_details.php?permit_no=${permit}`)
            .then(res => res.json())
            .then(data => {
                if (!data || Object.keys(data).length === 0) return;

                document.getElementById('originalPermit').value = data.permit_no;
                document.getElementById('editPermit').value = data.permit_no;
                document.getElementById('editName').value = data.bh_name;
                document.getElementById('editAddress').value = data.bh_address;

                // MAIN PHOTO
                const mainContainer = document.getElementById('currentMainPhoto');
                mainContainer.innerHTML = '';
                if(data.photos && data.photos.main) {
                    mainContainer.innerHTML = `
                        <div class="photo-wrapper" id="photo_${data.permit_no}_main">
                            <img src="${data.photos.main}" onclick="openLightbox('${data.photos.main}')">
                            <button type="button" class="btn-delete-photo" 
                                onclick="deleteDbPhoto('${data.photos.main}', '${data.permit_no}', 'photo_${data.permit_no}_main', true)">&times;</button>
                        </div>`;
                }

                // ADDITIONAL PHOTOS
                const extrasContainer = document.getElementById('existingExtrasContainer');
                extrasContainer.innerHTML = ''; 
                let currentExtraCount = 0;
                if(data.photos && data.photos.extras) {
                    currentExtraCount = data.photos.extras.length;
                    data.photos.extras.forEach((src, idx) => {
                        let divId = `db_photo_${idx}`;
                        extrasContainer.innerHTML += `
                            <div class="photo-wrapper" id="${divId}">
                                <img src="${src}" onclick="openLightbox('${src}')">
                                <button type="button" class="btn-delete-photo" 
                                    onclick="deleteDbPhoto('${src}', '${data.permit_no}', '${divId}', false)">&times;</button>
                            </div>`;
                    });
                }
                renderExtraInputs(5 - currentExtraCount);

                // POLICIES (Radio Buttons)
                let html = '';
                if(data.policies) {
                    data.policies.forEach((pol, index) => {
                        let yesChecked = pol.pol_stat === 'yes' ? 'checked' : '';
                        let noChecked = pol.pol_stat === 'no' ? 'checked' : '';
                        html += `
                            <div class="policy-item">
                                <div><strong>${pol.pol_name}</strong></div>
                                <div class="radio-group">
                                    <input type="hidden" name="policies[${index}][pol_id]" value="${pol.pol_id}">
                                    <label><input type="radio" name="policies[${index}][pol_stat]" value="yes" ${yesChecked}> Yes</label>
                                    <label><input type="radio" name="policies[${index}][pol_stat]" value="no" ${noChecked}> No</label>
                                </div>
                            </div>`;
                    });
                }
                document.getElementById('editPoliciesList').innerHTML = html;
            });
    }

    function renderExtraInputs(count) {
        const container = document.getElementById('extraInputsContainer');
        container.innerHTML = ''; 
        if (count <= 0) { container.innerHTML = '<small>Max photos reached.</small>'; return; }
        for (let i = 0; i < count; i++) {
            container.innerHTML += `<div class="file-input-item"><input type="file" name="extra_photos[]" accept="image/*" class="edit-input" style="margin-bottom:0;"></div>`;
        }
    }

    window.deleteDbPhoto = function(path, permit, divId, isMain) {
        if(!confirm("Permanently delete this photo?")) return;
        fetch('php/delete_bh_photo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ photo_path: path, permit_no: permit })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById(divId).remove();
                if(!isMain) {
                    const container = document.getElementById('extraInputsContainer');
                    const div = document.createElement('div');
                    div.className = 'file-input-item';
                    div.innerHTML = '<input type="file" name="extra_photos[]" accept="image/*" class="edit-input" style="margin-bottom:0;">';
                    container.appendChild(div);
                }
            } else { alert("Error: " + data.message); }
        });
    }

    document.getElementById('editBhForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if(!confirm("Save changes?")) return;
        const formData = new FormData(this); 
        const policyDivs = document.querySelectorAll('#editPoliciesList .policy-item');
        let policiesArr = [];
        policyDivs.forEach(div => {
            const idInput = div.querySelector('input[type="hidden"]');
            const checkedRadio = div.querySelector('input[type="radio"]:checked');
            if(idInput && checkedRadio) policiesArr.push({ pol_id: idInput.value, pol_stat: checkedRadio.value });
        });
        formData.append('policies', JSON.stringify(policiesArr));

        fetch('php/update_bh_details.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) { alert("Updated successfully!"); location.reload(); }
            else { alert("Error: " + data.message); }
        });
    });

    window.openLightbox = function(src) { document.getElementById('lightboxImg').src = src; document.getElementById('lightbox').style.display = 'flex'; }
    window.closeLightbox = function() { document.getElementById('lightbox').style.display = 'none'; }

    function closeAll() {
        document.getElementById('popupOverlay').style.display = 'none';
        document.getElementById('popupForm').style.display = 'none';
        document.getElementById('detailsModal').style.display = 'none';
    }
    document.getElementById('cancelBtn').addEventListener('click', closeAll);
    document.getElementById('closeDetailsBtn').addEventListener('click', closeAll);
    document.getElementById('popupOverlay').addEventListener('click', closeAll);

    window.addEventListener('DOMContentLoaded', () => {
      fetch('php/check_session.php').then(res => res.json()).then(data => {
          if (!data.loggedIn) window.location.href = 'login.php';
      }).catch(() => {});
    });
</script>
</body>
</html>