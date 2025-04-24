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
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400"); // Cache for 1 day
    exit(0);
}

// Set CORS headers for actual request
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$db = new MySQLConnector();

if (!isset($_SESSION["rb_user"])) {
    echo json_encode(["type" => "error", "message" => "Unauthorized access"]);
    exit;
}

// Check if user has permission (Assuming only admin can change article status)
$sessionAdmin = $_SESSION["rb_admin"] ?? false;

if (!$sessionAdmin) {
    echo json_encode(["type" => "error", "message" => "Permission denied"]);
    exit;
}

// Get the JSON input data
$data = json_decode(file_get_contents("php://input"), true);

$articleId = $data["article_id"] ?? null;
$newStatus = $data["status"] ?? null;

$allowedStatuses = ["draft", "published", "archived"];

if (empty($articleId) || empty($newStatus)) {
    echo json_encode(["type" => "error", "message" => "Missing required fields"]);
    exit;
}

if (!in_array($newStatus, $allowedStatuses)) {
    echo json_encode(["type" => "error", "message" => "Invalid status"]);
    exit;
}

// Check if the article exists
$sql = "SELECT * FROM articles WHERE id = ?";
$result = $db->search($sql, "i", [$articleId]);

if (count($result) == 0) {
    echo json_encode(["type" => "error", "message" => "Article not found"]);
    exit;
}

// Update article status
$updateSql = "UPDATE articles SET status = ? WHERE id = ?";
$updateResult = $db->iud($updateSql, "si", [$newStatus, $articleId]);

if ($updateResult["affected_rows"] > 0) {
    echo json_encode(["type" => "success", "message" => "Article status updated successfully"]);
} else {
    echo json_encode(["type" => "error", "message" => "Failed to update status"]);
}
?>
