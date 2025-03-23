<?php
session_start();
require "../../../config/MySQLConnector.php";

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400"); // Cache for 1 day
    exit(0);
}

// Set CORS headers for actual request
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$db = new MySQLConnector();

// Check if the admin is logged in
if (!isset($_SESSION["rb_admin"]) || $_SESSION["rb_admin"] !== true) {
    echo json_encode(["type" => "error", "message" => "Unauthorized access"]);
    exit;
}

// Fetch all articles with user details
$sql = "SELECT a.id, a.title, a.content, a.status, a.created_at, 
               u.id AS user_id, u.name AS user_name, u.email AS user_email
        FROM articles a
        JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC";

$result = $db->search($sql);

if (count($result) > 0) {
    echo json_encode(["type" => "success", "articles" => $result]);
} else {
    echo json_encode(["type" => "error", "message" => "No articles found"]);
}
?>
