<?php
session_start();
require "../../../config/MySQLConnector.php";

// Enable detailed error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

// Set CORS and Content-Type headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Initialize database connection and response object
$db = new MySQLConnector();
$response = new stdClass();

try {
    $tags = $db->search("SELECT * FROM tags ORDER BY id DESC");

    if (count($tags) > 0) {
        $response->type = "success";
        $response->message = "Tags retrieved successfully.";
        $response->data = $tags;
    } else {
        $response->type = "info";
        $response->message = "No tags found.";
        $response->data = [];
    }

} catch (Exception $e) {
    $response->type = "error";
    $response->message = "Server error: " . $e->getMessage();
}

echo json_encode($response);
