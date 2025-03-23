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

// Check if the admin is logged in
if (!isset($_SESSION["rb_admin"]) || $_SESSION["rb_admin"] !== true) {
    echo json_encode(["type" => "error", "message" => "Unauthorized access"]);
    exit;
}

// Validate input data
$article_id = $_POST["article_id"] ?? null;
$title = $_POST["title"] ?? null;
$content = $_POST["content"] ?? null;
$status = $_POST["status"] ?? null; // Status can be 'published', 'draft', 'archived'

$message = new stdClass();

if (empty($article_id)) {
    $message->type = "error";
    $message->message = "Article ID is required.";
    echo json_encode($message);
    exit;
}

if (empty($title)) {
    $message->type = "error";
    $message->message = "Title is required.";
    echo json_encode($message);
    exit;
}

if (empty($content)) {
    $message->type = "error";
    $message->message = "Content is required.";
    echo json_encode($message);
    exit;
}

if (!in_array($status, ['published', 'draft', 'archived'])) {
    $message->type = "error";
    $message->message = "Invalid status.";
    echo json_encode($message);
    exit;
}

// Check if the article exists
$sql = "SELECT * FROM articles WHERE id = ?";
$result = $db->search($sql, "i", [$article_id]);

if (count($result) === 0) {
    $message->type = "error";
    $message->message = "Article not found.";
    echo json_encode($message);
    exit;
}

// Update the article
$updateQuery = "UPDATE articles SET title = ?, content = ?, status = ? WHERE id = ?";
$updateResult = $db->iud($updateQuery, "sssi", [$title, $content, $status, $article_id]);

if ($updateResult['affected_rows'] > 0) {
    $message->type = "success";
    $message->message = "Article updated successfully.";
} else {
    $message->type = "error";
    $message->message = "Failed to update article.";
}

echo json_encode($message);
?>
