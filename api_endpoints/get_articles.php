<?php
session_start();
require "../../../config/MySQLConnector.php";

// Enable error reporting for debugging (disable in production)
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
    $articles = $db->search("SELECT * FROM articles ORDER BY published_date DESC");

    if (count($articles) > 0) {
        $response->type = "success";
        $response->message = "Articles fetched successfully.";
        $response->data = $articles;
    } else {
        $response->type = "info";
        $response->message = "No articles found.";
        $response->data = [];
    }

} catch (Exception $e) {
    $response->type = "error";
    $response->message = "Database error: " . $e->getMessage();
}

echo json_encode($response);
