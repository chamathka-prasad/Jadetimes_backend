<?php
session_start();
require "../../../config/MySQLConnector.php";

// CORS & Content-Type headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Initialize
$db = new MySQLConnector();
$response = new stdClass();

try {
    $users = $db->search("SELECT * FROM users ORDER BY id DESC");

    if (count($users) > 0) {
        $response->type = "success";
        $response->message = "Users retrieved successfully.";
        $response->data = $users;
    } else {
        $response->type = "info";
        $response->message = "No users found.";
        $response->data = [];
    }

} catch (Exception $e) {
    $response->type = "error";
    $response->message = "Server error: " . $e->getMessage();
}

echo json_encode($response);
