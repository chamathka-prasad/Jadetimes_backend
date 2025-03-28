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

if (!isset($_SESSION["rb_user"])) {
    echo json_encode(["type" => "error", "message" => "Unauthorized access"]);
    exit;
}

// Check if a specific article ID is requested
$articleId = isset($_GET["article_id"]) ? intval($_GET["article_id"]) : null;

if ($articleId) {
    // Fetch a single article by ID
    $sql = "SELECT * FROM articles WHERE id = ?";
    $result = $db->search($sql, "i", [$articleId]);

    if (count($result) > 0) {
        echo json_encode(["type" => "success", "article" => $result[0]]);
    } else {
        echo json_encode(["type" => "error", "message" => "Article not found"]);
    }
} else {
    // Fetch all articles
    $sql = "SELECT * FROM articles ORDER BY created_at DESC";
    $result = $db->search($sql);

    if (count($result) > 0) {
        echo json_encode(["type" => "success", "articles" => $result]);
    } else {
        echo json_encode(["type" => "error", "message" => "No articles found"]);
    }
}
?>
