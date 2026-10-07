<?php
// Database connection
$host = "localhost";
$dbname = "QELA_WEB";
$username = "root";
$password = "";

$connection = mysqli_connect($host,$username,$password,$dbname);

if (!$connection) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Unable to connect to the database.');
}

?>
