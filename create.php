<?php
session_start();
require "../../../config/MySQLConnector.php";
require "../../../service/mailService.php";

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400"); // Cache for 1 day
    exit(0);
}

// Set CORS headers for actual request
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Ensure the response is JSON
header("Content-Type: application/json");

$db = new MySQLConnector();

if (isset($_SESSION["rb_user"])) {
    // Retrieve and sanitize inputs
    $title = $_POST["title"] ?? "";
    $content = $_POST["content"] ?? "";
    $user_id = $_POST["user_id"] ?? "";
    $category_id = $_POST["category_id"] ?? "";
    $subcategory_id = $_POST["subcategory_id"] ?? NULL;
    $status = $_POST["status"] ?? "draft";
    
    // New fields
    $image_source = $_POST["image_source"] ?? "";
    $image_alt_text = $_POST["image_alt_text"] ?? "";
    $image_left_align = isset($_POST["image_left_align"]) ? (bool)$_POST["image_left_align"] : false;
    $isEditing = isset($_POST["isEditing"]) ? (bool)$_POST["isEditing"] : false;

    $message = new stdClass();

    // Validate input fields
    if (empty($title)) {
        $message->type = "error";
        $message->message = "Title is empty";
        echo json_encode($message);
        exit();
    }

    if (empty($content)) {
        $message->type = "error";
        $message->message = "Content is empty";
        echo json_encode($message);
        exit();
    }

    $sql = "SELECT * FROM articles WHERE title = ?";
    $result = $db->search($sql, 's', [$title]);

    if (count($result) == 0) {
        $imagePath = "";

        // Handle image upload if exists
        if (isset($_FILES["image"])) {
            $img = $_FILES["image"];
            $ext = pathinfo($img["name"], PATHINFO_EXTENSION);
            $fileName = uniqid();
            $imagePath = $fileName . "." . $ext;
            $path = "../../../resources/articleImages/" . $imagePath;
            move_uploaded_file($img["tmp_name"], $path);
        }

        // Insert article with additional fields
        $insertArticle = $db->iud(
            "INSERT INTO articles (title, content, image, image_source, image_alt_text, image_left_align, isEditing, user_id, category_id, subcategory_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
            "sssssssisii",
            [$title, $content, $imagePath, $image_source, $image_alt_text, $image_left_align, $isEditing, $user_id, $category_id, $subcategory_id, $status]
        );

        if ($insertArticle['affected_rows'] > 0) {
            $body = "Your article has been successfully created.";
            MailSender::sendMail($_SESSION["rb_user"], "Article Published", $body);

            $message->type = "success";
            $message->message = "Article created successfully";
            echo json_encode($message);
        } else {
            $message->type = "error";
            $message->message = "Insert Error";
            echo json_encode($message);
        }
    } else {
        $message->type = "error";
        $message->message = "Article with this title already exists.";
        echo json_encode($message);
    }
} else {
    $message = new stdClass();
    $message->type = "error";
    $message->message = "Unauthorized access";
    echo json_encode($message);
}
