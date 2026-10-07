<?php
// Database connection
$host = "localhost";
$dbname = "qelatech_QELA_WEB";
$username = "qelatech_Qelatechnologies";
$password = "Qelatechnologies@2017";

$connection = mysqli_connect($host,$username,$password,$dbname);

if (!$connection) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Unable to connect to the database.');
}

?>
