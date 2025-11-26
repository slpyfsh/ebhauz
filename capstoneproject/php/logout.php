<?php
session_start();
session_unset();
session_destroy();

// Optional: clear the remember_me cookie
setcookie('remember_me', '', time() - 3600, '/');

header("Location: ../viewer.php");
exit;
