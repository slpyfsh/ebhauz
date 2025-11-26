<?php 
session_start();
include 'php/connection.php'; 

$sql = "SELECT b.permit_no, b.bh_name, b.bh_address,
        (SELECT photo_path FROM bh_photos WHERE permit_no = b.permit_no AND photo_type = 'main' LIMIT 1) as main_photo
        FROM bh_table b 
        WHERE b.accred_status = 'yes'
        ORDER BY b.bh_name ASC";

$result = $conn->query($sql);
$boardingHouses = [];
if($result) {
    while ($row = $result->fetch_assoc()) {
        $boardingHouses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <title>Accredited Boarding Houses</title>
  <link rel="stylesheet" href="css/form.css" />
  
  <style>
    /* GLOBAL LAYOUT */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #fbfcfd;
        padding-top: 80px; 
        margin: 0;
        display: block; /* Ensures vertical stacking */
    }

    /* Header Container for Left Alignment */
    .header-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px 40px 0 40px; /* Matches grid padding */
        text-align: left; /* Left Align */
    }

    h1 { 
        color: #4e73df; 
        margin: 0 0 5px 0; 
        font-size: 2rem;
    }
    
    p.subtitle { 
        color: #666; 
        margin: 0 0 20px 0; 
        font-size: 1rem;
    }

    /* Cards Grid */
    .cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
        gap: 30px;
        padding: 20px 40px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* MOBILE ADJUSTMENTS */
    @media (max-width: 768px) {
        .header-content { padding: 20px 15px 0 15px; }
        h1 { font-size: 1.5rem; }
        .cards-container {
            grid-template-columns: 1fr; 
            gap: 20px;
            padding: 15px; 
        }
        .card-image { height: 220px !important; }
    }

    /* Card Styles */
    .bh-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s;
    }
    @media (min-width: 769px) {
        .bh-card:hover { transform: translateY(-5px); }
    }

    .card-image {
        height: 200px;
        background-color: #e0e0e0;
        background-size: cover;
        background-position: center;
        cursor: zoom-in;
        position: relative;
    }

    .card-name {
        padding: 18px;
        text-align: center; font-weight: 700; color: white;
        background: #4e73df; font-size: 1.15rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        cursor: pointer;
    }
    .card-menu {
        position: absolute; top: 10px; right: 10px;
        width: 34px; height: 34px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 50%; text-align: center; line-height: 34px;
        font-size: 20px; font-weight: bold; color: #333;
        z-index: 100; cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    /* Modal & Lightbox Styles (Same as before) */
    .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; display: none; backdrop-filter: blur(2px); }
    .details-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; width: 90%; max-width: 500px; border-radius: 12px; z-index: 11000; display: none; max-height: 85vh; overflow-y: auto; }
    .details-modal h3 { color: #4e73df; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .detail-label { font-weight: 600; color: #555; }
    .btn-close { background: #e74a3b; color: white; border: none; width: 100%; padding: 12px; margin-top: 15px; border-radius: 8px; font-weight: bold; }
    .badge { padding: 3px 8px; border-radius: 4px; font-size: 0.8em; color: white; }
    .bg-green { background: #1cc88a; } .bg-red { background: #e74a3b; }
    .policy-list { list-style: none; padding: 0; }
    .policy-list li { background: #f8f9fa; padding: 8px; margin-bottom: 5px; border-radius: 4px; display: flex; justify-content: space-between; font-size: 0.9rem; }

    /* Lightbox */
    .lightbox-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 20000; display: none; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
    .lightbox-content { max-width: 100%; max-height: 60vh; border-radius: 4px; object-fit: contain; }
    .lightbox-close { position: absolute; top: 15px; right: 20px; color: white; font-size: 35px; cursor: pointer; z-index: 20002; }
    .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; font-size: 20px; cursor: pointer; z-index: 20001; backdrop-filter: blur(4px); display:flex; align-items:center; justify-content:center; }
    .prev { left: 15px; } .next { right: 15px; }
    .photo-counter { position: absolute; bottom: 30px; background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; color: white; font-size: 0.9rem; font-weight: 600; backdrop-filter: blur(4px); }
  </style>
</head>
<body>

  <?php include 'php/navbar.php'; ?>

  <div class="header-content">
      <h1>Accredited Boarding Houses</h1>
      <p class="subtitle">Browse our verified listings</p>
  </div>

  <div class="cards-container" id="cardsContainer">
    <?php if (empty($boardingHouses)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 20px;">No accredited boarding houses found.</p>
    <?php else: ?>
        <?php foreach ($boardingHouses as $bh): 
            $bgImage = $bh['main_photo'] ? $bh['main_photo'] : 'assets/default_house.jpg'; 
        ?>
            <div class="bh-card" data-name="<?= strtolower(htmlspecialchars($bh['bh_name'])) ?>">
                <div class="card-menu" onclick="openDetails('<?= $bh['permit_no'] ?>')">&#8942;</div>
                <div class="card-image" 
                     style="background-image: url('<?= $bgImage ?>');"
                     onclick="openLightbox('<?= $bh['permit_no'] ?>')">
                </div>
                <div class="card-name" onclick="openDetails('<?= $bh['permit_no'] ?>')">
                    <?= htmlspecialchars($bh['bh_name']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="popup-overlay" id="popupOverlay" onclick="closeAll()"></div>

  <div class="details-modal" id="detailsModal">
      <h3>Boarding House Details</h3>
      <div id="detailsContent">Loading...</div>
      <button class="btn-close" onclick="closeAll()">Close</button>
  </div>

  <div id="lightbox" class="lightbox-modal">
      <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
      <button class="slider-btn prev" onclick="changePhoto(-1)">&#10094;</button>
      <img id="lightboxImg" class="lightbox-content" src="">
      <button class="slider-btn next" onclick="changePhoto(1)">&#10095;</button>
      <div class="photo-counter" id="photoCounter"></div>
  </div>

<script>
    // --- SEARCH FUNCTIONALITY (NEW) ---
    // Listen for input on the navbar search box
    const searchInput = document.getElementById('navSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const filter = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.bh-card');
            
            cards.forEach(card => {
                const name = card.dataset.name; // Get name from data attribute
                if (name.includes(filter)) {
                    card.style.display = 'flex'; // Show
                } else {
                    card.style.display = 'none'; // Hide
                }
            });
        });
    }

    // --- DETAILS LOGIC ---
    function openDetails(permit) {
        document.getElementById('popupOverlay').style.display = 'block';
        document.getElementById('detailsModal').style.display = 'block';
        document.getElementById('detailsContent').innerHTML = '<p style="text-align:center;">Loading details...</p>';

        fetch(`php/get_bh_details.php?permit_no=${permit}`)
            .then(res => res.json())
            .then(data => {
                if (!data || Object.keys(data).length === 0) {
                    document.getElementById('detailsContent').innerHTML = '<p style="color:red;">Error loading details.</p>';
                    return;
                }
                let html = `
                    <div class="detail-row"><span class="detail-label">Name:</span> <span class="detail-value"><strong>${data.bh_name}</strong></span></div>
                    <div class="detail-row"><span class="detail-label">Address:</span> <span class="detail-value">${data.bh_address}</span></div>
                    <h4 style="margin: 15px 0 10px; color:#4e73df;">Owner Info</h4>
                    <div class="detail-row"><span class="detail-label">Name:</span> <span class="detail-value">${data.first_name} ${data.last_name}</span></div>
                    <div class="detail-row"><span class="detail-label">Contact:</span> <span class="detail-value">${data.cont_no}</span></div>
                    <h4 style="margin: 15px 0 10px; color:#4e73df;">Policies</h4>
                    <ul class="policy-list">`;
                if(data.policies && data.policies.length > 0) {
                    data.policies.forEach(pol => {
                        let color = pol.pol_stat === 'yes' ? 'bg-green' : 'bg-red';
                        html += `<li><span>${pol.pol_name}</span><span class="badge ${color}">${pol.pol_stat.toUpperCase()}</span></li>`;
                    });
                } else { html += `<li>No policies listed.</li>`; }
                html += `</ul>`;
                document.getElementById('detailsContent').innerHTML = html;
            });
    }

    // --- LIGHTBOX LOGIC (Same as before) ---
    let currentPhotos = [];
    let currentIndex = 0;

    function openLightbox(permit) {
        document.getElementById('lightbox').style.display = 'flex';
        document.getElementById('lightboxImg').src = ''; 
        document.getElementById('photoCounter').innerText = 'Loading...';

        fetch(`php/get_bh_details.php?permit_no=${permit}`)
            .then(res => res.json())
            .then(data => {
                currentPhotos = [];
                if(data.photos && data.photos.main) currentPhotos.push(data.photos.main);
                if(data.photos && data.photos.extras) {
                    data.photos.extras.forEach(p => currentPhotos.push(p));
                }
                if(currentPhotos.length === 0) {
                    alert("No photos available."); closeLightbox(); return;
                }
                currentIndex = 0;
                updateLightboxImage();
            })
            .catch(err => { console.error(err); closeLightbox(); });
    }

    function updateLightboxImage() {
        if(currentPhotos.length === 0) return;
        document.getElementById('lightboxImg').src = currentPhotos[currentIndex];
        document.getElementById('photoCounter').innerText = `${currentIndex + 1} / ${currentPhotos.length}`;
    }

    function changePhoto(direction) {
        if(currentPhotos.length <= 1) return;
        currentIndex += direction;
        if(currentIndex >= currentPhotos.length) currentIndex = 0;
        if(currentIndex < 0) currentIndex = currentPhotos.length - 1;
        updateLightboxImage();
    }

    function closeLightbox() { document.getElementById('lightbox').style.display = 'none'; }
    function closeAll() {
        document.getElementById('popupOverlay').style.display = 'none';
        document.getElementById('detailsModal').style.display = 'none';
    }
    
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('lightbox').style.display === 'flex') {
            if (e.key === 'ArrowLeft') changePhoto(-1);
            if (e.key === 'ArrowRight') changePhoto(1);
            if (e.key === 'Escape') closeLightbox();
        }
    });
</script>

</body>
</html>
