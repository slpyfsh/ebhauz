<?php
if (getenv('RENDER')) {
    $servername = getenv('DB_HOST');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $dbname= getenv('DB_NAME');
    $port= getenv('DB_PORT');
} else {
    $servername = "mysql-3d5e5d74-ebhauz.g.aivencloud.com";
    $username = "avnadmin";
    $password = "AVNS_m_PsbL-awOWQiU7oNGW";
    $dbname = "defaultdb";
    $port = 23824;
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>