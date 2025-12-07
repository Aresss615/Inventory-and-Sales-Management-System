<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "invsys";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    echo "Not connected!";
}

?>
