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

// Retrieve and validate input data
$action = $_POST["action"] ?? null; // create_category, edit_category, create_subcategory, edit_subcategory
$category_id = $_POST["category_id"] ?? null;
$subcategory_id = $_POST["subcategory_id"] ?? null;
$name = $_POST["name"] ?? null;
$parent_id = $_POST["parent_id"] ?? null; // For subcategories

$message = new stdClass();

if (empty($action)) {
    $message->type = "error";
    $message->message = "Action is required.";
    echo json_encode($message);
    exit;
}

if (empty($name)) {
    $message->type = "error";
    $message->message = "Category/Subcategory name is required.";
    echo json_encode($message);
    exit;
}

if ($action === "create_category") {
    // Check if category exists
    $sql = "SELECT * FROM categories WHERE name = ?";
    $result = $db->search($sql, "s", [$name]);

    if (count($result) > 0) {
        $message->type = "error";
        $message->message = "Category name already exists.";
    } else {
        $insert = $db->iud("INSERT INTO categories (name) VALUES (?)", "s", [$name]);
        if ($insert['affected_rows'] > 0) {
            $message->type = "success";
            $message->message = "Category created successfully.";
        } else {
            $message->type = "error";
            $message->message = "Failed to create category.";
        }
    }

} elseif ($action === "edit_category" && $category_id) {
    // Check if category exists
    $sql = "SELECT * FROM categories WHERE id = ?";
    $result = $db->search($sql, "i", [$category_id]);

    if (count($result) === 0) {
        $message->type = "error";
        $message->message = "Category not found.";
    } else {
        $update = $db->iud("UPDATE categories SET name = ? WHERE id = ?", "si", [$name, $category_id]);
        if ($update['affected_rows'] > 0) {
            $message->type = "success";
            $message->message = "Category updated successfully.";
        } else {
            $message->type = "error";
            $message->message = "Failed to update category.";
        }
    }

} elseif ($action === "create_subcategory" && $parent_id) {
    // Check if parent category exists
    $sql = "SELECT * FROM categories WHERE id = ?";
    $result = $db->search($sql, "i", [$parent_id]);

    if (count($result) === 0) {
        $message->type = "error";
        $message->message = "Parent category not found.";
    } else {
        // Check if subcategory exists
        $sql = "SELECT * FROM subcategories WHERE name = ? AND category_id = ?";
        $result = $db->search($sql, "si", [$name, $parent_id]);

        if (count($result) > 0) {
            $message->type = "error";
            $message->message = "Subcategory name already exists.";
        } else {
            $insert = $db->iud("INSERT INTO subcategories (name, category_id) VALUES (?, ?)", "si", [$name, $parent_id]);
            if ($insert['affected_rows'] > 0) {
                $message->type = "success";
                $message->message = "Subcategory created successfully.";
            } else {
                $message->type = "error";
                $message->message = "Failed to create subcategory.";
            }
        }
    }

} elseif ($action === "edit_subcategory" && $subcategory_id) {
    // Check if subcategory exists
    $sql = "SELECT * FROM subcategories WHERE id = ?";
    $result = $db->search($sql, "i", [$subcategory_id]);

    if (count($result) === 0) {
        $message->type = "error";
        $message->message = "Subcategory not found.";
    } else {
        $update = $db->iud("UPDATE subcategories SET name = ? WHERE id = ?", "si", [$name, $subcategory_id]);
        if ($update['affected_rows'] > 0) {
            $message->type = "success";
            $message->message = "Subcategory updated successfully.";
        } else {
            $message->type = "error";
            $message->message = "Failed to update subcategory.";
        }
    }

} else {
    $message->type = "error";
    $message->message = "Invalid request.";
}

echo json_encode($message);
?>
