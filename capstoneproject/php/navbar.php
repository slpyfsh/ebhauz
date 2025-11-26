<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userRole = $_SESSION['role'] ?? 'viewer';
$currentPage = basename($_SERVER['PHP_SELF']);

// Determine where the Logo points to
$logoLink = $currentPage; // Default: Refresh current page (for admin/viewer)
if ($userRole === 'owner') {
    $logoLink = 'home.php';
}
?>

<nav class="navbar">
    <button class="hamburger" id="hamburgerBtn">☰</button>

    <a href="<?php echo $logoLink; ?>" class="nav-logo">EBHaus</a>

    <?php if ($currentPage === 'index.php'): ?>
        <div class="nav-search">
            <input type="text" id="navSearchInput" placeholder="Search..." />
        </div>
    <?php endif; ?>

    <ul class="nav-links" id="navLinks">
        <?php if ($userRole === "viewer"): ?>
            <li>
                <a href="login.php">
                    <img src="assets/icons/login.svg" class="nav-icon" alt="icon"> Login
                </a>
            </li>

        <?php elseif ($userRole === "owner"): ?>
            <li>
                <a href="home.php">
                    <img src="assets/icons/home.svg" class="nav-icon" alt="icon"> Home
                </a>
            </li>
            <li>
                <a href="profile.php">
                    <img src="assets/icons/user.svg" class="nav-icon" alt="icon"> Profile
                </a>
            </li>
            <li>
                <a href="php/logout.php">
                    <img src="assets/icons/logout.svg" class="nav-icon" alt="icon"> Logout
                </a>
            </li>

        <?php elseif ($userRole === "admin"): ?>
            <li>
                <a href="php/logout.php">
                    <img src="assets/icons/logout.svg" class="nav-icon" alt="icon"> Logout
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<style>
/* =====================
   DESKTOP STYLES
===================== */
.navbar {
    display: flex;
    align-items: center;
    background: #4e73df;
    padding: 0 20px; 
    color: white;
    position: fixed;
    top: 0; left: 0; width: 100%;
    z-index: 2000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    height: 70px;
    box-sizing: border-box;
    gap: 15px;
}

/* Updated Logo Styles for Link */
.nav-logo {
    font-size: 22px;
    font-weight: 700;
    white-space: nowrap;
    margin-right: auto;
    color: white;           /* Force white color */
    text-decoration: none;  /* Remove underline */
    cursor: pointer;
    transition: opacity 0.2s;
}
.nav-logo:hover {
    opacity: 0.9;
}

/* Search Bar */
.nav-search {
    flex-grow: 0;
    width: 300px; 
    margin: 0 20px;
}
.nav-search input {
    width: 100%;
    padding: 8px 15px;
    border-radius: 20px;
    border: none;
    outline: none;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transition: background 0.2s;
}
.nav-search input::placeholder { color: rgba(255, 255, 255, 0.8); }
.nav-search input:focus { background: rgba(255, 255, 255, 0.35); }

/* Links & Icons */
.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
    margin: 0;
    padding: 0;
}

.nav-links li a {
    color: white;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px; 
}

.nav-icon {
    width: 20px;
    height: 20px;
    filter: brightness(0) invert(1); 
}

.hamburger {
    display: none;
    background: transparent;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
}

/* =====================
   MOBILE STYLES
===================== */
@media (max-width: 768px) {
    .navbar {
        padding: 0 10px; 
        gap: 5px; 
        justify-content: flex-start;
    }
    
    .hamburger {
        display: block;
        margin: 0; 
        padding: 5px;
        line-height: 1;
        width: auto; 
    }

    .nav-logo {
        font-size: 1.2rem;
        margin-left: 2px; 
        margin-right: auto;
    }
    
    .nav-search {
        width: auto;
        max-width: 140px; 
        margin: 0;
    }
    .nav-search input { padding: 6px 10px; font-size: 13px; }

    .nav-links {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: #4e73df;
        flex-direction: column;
        padding: 0;
        box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        margin-top: 0;
    }

    .nav-links.show { display: flex; }

    .nav-links li { width: 100%; text-align: center; }
    .nav-links li a {
        justify-content: center; 
        padding: 15px 0;
        width: 100%;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
}
</style>

<script>
    document.getElementById("hamburgerBtn").addEventListener("click", () => {
        document.getElementById("navLinks").classList.toggle("show");
    });
</script>