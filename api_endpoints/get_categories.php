<?php
session_start();
require "../../../config/MySQLConnector.php";

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

// Set CORS headers for actual request
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$db = new MySQLConnector();
$response = new stdClass();

try {
    $categories = $db->search("SELECT * FROM categories ORDER BY name ASC");

    if (count($categories) > 0) {
        $response->type = "success";
        $response->message = "Categories retrieved successfully.";
        $response->data = $categories;
    } else {
        $response->type = "info";
        $response->message = "No categories found.";
        $response->data = [];
    }

} catch (Exception $e) {
    $response->type = "error";
    $response->message = "Server error: " . $e->getMessage();
}

echo json_encode($response);
