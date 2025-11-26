<?php
//$servername = "localhost";
//$username = "root";
//$password = "";
//$dbname = "ebhauz";

//$conn = new mysqli($servername, $username, $password, $dbname);
//if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}
// Note: Render sets the 'RENDER' environment variable.
if (getenv('RENDER')) {
    // PRODUCTION SETTINGS (Pulled from Render dashboard Environment Variables)
    $servername = getenv('DB_HOST');
    $username   = getenv('DB_USER');
    $password   = getenv('DB_PASS');
    $dbname     = getenv('DB_NAME');
    $port       = getenv('DB_PORT');
} else {
    // LOCAL SETTINGS (For XAMPP/WAMP testing)
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "ebhauz";
    $port       = 3306;
}

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    // Note: On Render, this will print to the deployment logs, not the user screen
    die("Connection failed: " . $conn->connect_error);
}
?>