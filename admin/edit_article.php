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

// Check if user has permission (Assuming only admin or article owner can edit)
$sessionAdmin = $_SESSION["rb_admin"] ?? false;

// Get the JSON input data
$data = json_decode(file_get_contents("php://input"), true);

$articleId = $data["article_id"] ?? null;
$title = $data["title"] ?? null;
$content = $data["content"] ?? null;
$status = $data["status"] ?? null;

$allowedStatuses = ["draft", "published", "archived"];

if (empty($articleId) || empty($title) || empty($content) || empty($status)) {
    echo json_encode(["type" => "error", "message" => "Missing required fields"]);
    exit;
}

if (!in_array($status, $allowedStatuses)) {
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

// Check if the user is the owner or an admin
$article = $result[0];

if (!$sessionAdmin && $_SESSION["rb_user"] !== $article["author_id"]) {
    echo json_encode(["type" => "error", "message" => "Permission denied"]);
    exit;
}

// Update article
$updateSql = "UPDATE articles SET title = ?, content = ?, status = ? WHERE id = ?";
$updateResult = $db->iud($updateSql, "sssi", [$title, $content, $status, $articleId]);

if ($updateResult["affected_rows"] > 0) {
    echo json_encode(["type" => "success", "message" => "Article updated successfully"]);
} else {
    echo json_encode(["type" => "error", "message" => "Failed to update article"]);
}
?>
