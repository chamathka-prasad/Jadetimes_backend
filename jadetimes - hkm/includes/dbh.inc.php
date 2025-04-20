<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "jadetimes";

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

