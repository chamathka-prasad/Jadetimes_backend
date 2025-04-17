<?php
session_start();
require "../../../config/MySQLConnector.php";

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    exit(0);
}

// CORS and JSON headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$response = new stdClass();

// Authentication check
if (!isset($_SESSION["rb_user"])) {
    $response->type = "error";
    $response->message = "Unauthorized access.";
    echo json_encode($response);
    exit;
}

$db = new MySQLConnector();

// Collect form inputs
$title          = trim($_POST["Title"] ?? '');
$image_alt      = trim($_POST["Image alt text"] ?? '');
$image_src      = trim($_POST["Image source"] ?? '');
$image_align    = trim($_POST["Image leftalign"] ?? '');
$content        = trim($_POST["Content"] ?? '');
$date           = trim($_POST["Date"] ?? '');
$main_category  = trim($_POST["Main category"] ?? '');
$sub_categories = $_POST["Sub categories"] ?? []; // Assuming an array
$published_date = trim($_POST["Published date"] ?? '');

$image_path = "";

// Validate required fields
if (empty($title) || empty($content) || empty($main_category)) {
    $response->type = "error";
    $response->message = "Title, content, and main category are required.";
    echo json_encode($response);
    exit;
}

// Handle image upload
if (isset($_FILES["Image"]) && $_FILES["Image"]["error"] === UPLOAD_ERR_OK) {
    $img = $_FILES["Image"];
    $ext = pathinfo($img["name"], PATHINFO_EXTENSION);
    $file_name = uniqid("article_") . "." . $ext;
    $upload_path = "../../../resources/article_images/" . $file_name;

    if (move_uploaded_file($img["tmp_name"], $upload_path)) {
        $image_path = $file_name;
    } else {
        $response->type = "error";
        $response->message = "Image upload failed.";
        echo json_encode($response);
        exit;
    }
}

// Insert article
$insert_sql = "INSERT INTO articles 
    (title, image, image_alt, image_src, image_align, content, date, main_category, published_date, author_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$author_id = $_SESSION["rb_user"]["id"]; // Assuming this is set

$insert_result = $db->iud(
    $insert_sql,
    "sssssssssi",
    [
        $title,
        $image_path,
        $image_alt,
        $image_src,
        $image_align,
        $content,
        $date,
        $main_category,
        $published_date,
        $author_id
    ]
);

if ($insert_result['affected_rows'] > 0) {
    $article_id = $insert_result['insert_id'];

    // Insert subcategories
    if (!empty($sub_categories) && is_array($sub_categories)) {
        $sub_stmt = $db->getConnection()->prepare("INSERT INTO article_subcategories (article_id, subcategory) VALUES (?, ?)");
        foreach ($sub_categories as $sub) {
            $sub_stmt->bind_param("is", $article_id, $sub);
            $sub_stmt->execute();
        }
    }

    $response->type = "success";
    $response->message = "Article saved successfully.";
    $response->article_id = $article_id;
} else {
    $response->type = "error";
    $response->message = "Failed to save article.";
}

echo json_encode($response);
?>
