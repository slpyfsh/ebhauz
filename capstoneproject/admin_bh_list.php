<?php
session_start();
include 'php/connection.php';
// Navbar is included inside the body below

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$query = "SELECT 
            b.permit_no, 
            b.bh_name, 
            b.bh_address, 
            b.accred_status, 
            ov.first_name, 
            ov.last_name,
            ot.cont_no 
          FROM bh_table b 
          JOIN owner_ver ov ON b.owner_id = ov.owner_id
          JOIN owner_table ot ON b.owner_id = ot.owner_id
          ORDER BY b.bh_name ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Boarding Houses</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* --- DESKTOP STYLES --- */
        body {
            background-color: #f8f9fa;
            padding-top: 80px; 
            font-size: 16px;
        }

        .modal { z-index: 100050 !important; }
        .modal-backdrop { z-index: 100040 !important; }

        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 12px;
        }
        
        /* Status Dropdown */
        .status-select {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            min-width: 110px;
        }
        .status-select.accredited { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-select.denied { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .status-select.pending { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }

        /* Button Default */
        .btn-action {
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 4px;
            display: inline-block; 
            width: auto;
        }

        /* --- MOBILE STYLES --- */
        @media (max-width: 768px) {
            body { padding-top: 70px; font-size: 12px; }
            
            .container-fluid { padding: 0 5px; }
            .card { padding: 10px !important; }
            h2 { font-size: 1.3rem; margin-bottom: 10px !important; }

            .table td, .table th {
                padding: 8px 4px !important;
                vertical-align: middle;
            }

            /* Compact Buttons */
            .btn-action {
                display: inline-block;
                width: auto;
                margin: 0 2px;
                font-size: 11px;
                padding: 4px 8px;
            }
            
            /* Smaller Dropdown */
            .status-select {
                font-size: 11px;
                padding: 4px;
                min-width: auto;
            }
        }
    </style>
</head>
<body>

<?php include 'php/navbar.php'; ?>

<div class="container-fluid px-4 mb-5">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary">Registered Boarding Houses</h2>
        </div>

        <table id="bhTable" class="table table-hover align-middle" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th class="d-none d-md-table-cell">Permit No</th>
                    <th>Boarding House</th>
                    <th class="d-none d-md-table-cell">Owner</th>
                    <th class="d-none d-md-table-cell">Contact</th>
                    <th style="width: 100px;">Accreditation</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $ownerName = $row['first_name'] . ' ' . $row['last_name'];
                    $rawStatus = strtolower(trim($row['accred_status']));
                    $displayStatus = 'pending';
                    if($rawStatus == 'yes' || $rawStatus == 'active' || $rawStatus == 'accredited') $displayStatus = 'accredited';
                    elseif($rawStatus == 'no' || $rawStatus == 'denied') $displayStatus = 'denied';
                ?>
                <tr>
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['permit_no']) ?></td>
                    
                    <td>
                        <strong><?= htmlspecialchars($row['bh_name']) ?></strong><br>
                        <small class="text-muted d-none d-md-block"><?= htmlspecialchars($row['bh_address']) ?></small>
                        <small class="text-muted d-md-none" style="display:block; margin-top:2px; font-size:0.85em;">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($ownerName) ?>
                        </small>
                    </td>
                    
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($ownerName) ?></td>
                    
                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['cont_no']) ?></td>
                    
                    <td>
                        <select class="form-select status-select <?= $displayStatus ?>" 
                                data-permit="<?= $row['permit_no'] ?>" 
                                onchange="updateStatus(this)">
                            <option value="pending" <?= ($displayStatus == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="accredited" <?= ($displayStatus == 'accredited') ? 'selected' : '' ?>>Accredited</option>
                            <option value="denied"  <?= ($displayStatus == 'denied') ? 'selected' : '' ?>>Denied</option>
                        </select>
                    </td>

                    <td style="white-space: nowrap;">
                        <button class="btn btn-primary btn-sm btn-action view-btn" 
                                data-permit="<?= $row['permit_no'] ?>">
                            <span class="d-md-none">View</span>
                            <span class="d-none d-md-inline">View Details</span>
                        </button>

                        <button class="btn btn-warning btn-sm btn-action reset-pass-btn" 
                                data-permit="<?= $row['permit_no'] ?>">
                            <span class="d-md-none">Reset</span>
                            <span class="d-none d-md-inline">Reset Password</span>
                        </button>

                        <button class="btn btn-danger btn-sm btn-action delete-btn" 
                                data-permit="<?= $row['permit_no'] ?>">
                            <span class="d-md-none">Delete</span>
                            <span class="d-none d-md-inline">Delete</span>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Boarding House Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detailsBody">
        <div class="text-center py-3"><div class="spinner-border text-primary"></div></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="resetPassModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Owner Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="resetPassForm">
            <input type="hidden" id="resetPermitNo" name="permit_no">
            <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="text" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Update Password</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with autoWidth false to prevent mobile glitches
    $('#bhTable').DataTable({ 
        "order": [], 
        "pageLength": 10,
        "autoWidth": false 
    });

    // View Logic
    $(document).on('click', '.view-btn', function() {
        var permit = $(this).data('permit');
        var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();
        $('#detailsBody').html('<div class="text-center py-3"><div class="spinner-border text-primary"></div><br>Loading details...</div>');
        $.ajax({
            url: 'php/get_bh_details.php',
            method: 'GET',
            data: { permit_no: permit },
            dataType: 'json',
            success: function(data) {
                if(!data || Object.keys(data).length === 0) {
                    $('#detailsBody').html('<p class="text-danger text-center">No details found.</p>');
                    return;
                }
                let displayStatus = data.accred_status.toLowerCase();
                if(displayStatus === 'yes') displayStatus = 'Accredited';
                else if(displayStatus === 'no') displayStatus = 'Denied';
                else displayStatus = 'Pending';

                var html = `
                    <div class="container-fluid">
                        <h6 class="text-primary fw-bold">Owner Information</h6>
                        <p class="mb-1"><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                        <p class="mb-1"><strong>Contact:</strong> ${data.cont_no}</p>
                        <p class="mb-3"><strong>Address:</strong> ${data.owner_address}</p>
                        <hr>
                        <h6 class="text-primary fw-bold">Boarding House Info</h6>
                        <p class="mb-1"><strong>Name:</strong> ${data.bh_name}</p>
                        <p class="mb-1"><strong>Address:</strong> ${data.bh_address}</p>
                        <p class="mb-3"><strong>Status:</strong> ${displayStatus}</p>
                        <hr>
                        <h6 class="text-primary fw-bold">Policies</h6>
                        <ul class="list-group list-group-flush">`;
                if(data.policies && data.policies.length > 0) {
                    data.policies.forEach(function(pol) {
                        var badge = pol.pol_stat === 'yes' ? 'bg-success' : 'bg-secondary';
                        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${pol.pol_name}
                                    <span class="badge ${badge}">${pol.pol_stat}</span>
                                 </li>`;
                    });
                } else { html += `<li class="list-group-item text-muted">No policies recorded.</li>`; }
                html += `</ul></div>`;
                $('#detailsBody').html(html);
            },
            error: function() { $('#detailsBody').html('<p class="text-danger text-center">Error fetching data.</p>'); }
        });
    });

    // Delete Logic
    $(document).on('click', '.delete-btn', function() {
        var permit = $(this).data('permit');
        if(confirm('Are you sure you want to delete this Boarding House?')) {
            $.ajax({
                url: 'php/delete_bh.php',
                method: 'POST',
                data: { permit_no: permit },
                dataType: 'json',
                success: function(response) {
                    if(response.success) { alert('Deleted successfully.'); location.reload(); }
                    else { alert('Error: ' + response.message); }
                },
                error: function() { alert('Server error.'); }
            });
        }
    });

    // Reset Password Logic
    $(document).on('click', '.reset-pass-btn', function() {
        var permit = $(this).data('permit');
        $('#resetPermitNo').val(permit);
        $('#newPassword').val('');
        new bootstrap.Modal(document.getElementById('resetPassModal')).show();
    });

    $('#resetPassForm').on('submit', function(e) {
        e.preventDefault();
        if(!confirm("Change owner's password?")) return;
        $.ajax({
            url: 'php/admin_reset_password.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) { alert('Password updated!'); location.reload(); }
                else { alert('Error: ' + response.message); }
            },
            error: function() { alert('Server error.'); }
        });
    });
});

function updateStatus(selectElement) {
    var newStatus = selectElement.value;
    var permit = selectElement.getAttribute('data-permit');
    selectElement.className = 'form-select status-select ' + newStatus;
    if(!confirm('Change status to ' + newStatus + '?')) { location.reload(); return; }
    $.ajax({
        url: 'php/update_bh_accredit.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ permit_no: permit, accred_status: newStatus }),
        success: function(response) {
            if(response.success) console.log("Status updated");
            else { alert("Error: " + response.message); location.reload(); }
        },
        error: function() { alert("Connection failed."); location.reload(); }
    });
}
</script>

</body>
</html>