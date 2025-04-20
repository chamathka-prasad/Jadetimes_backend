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

// Actual CORS and headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$response = new stdClass();
$db = new MySQLConnector();

$username = trim($_POST["username"] ?? '');
$email    = trim($_POST["email"] ?? '');
$password = trim($_POST["pwd"] ?? '');
$role     = trim($_POST["role"] ?? '');

if (empty($username) || empty($email) || empty($password) || empty($role)) {
    $response->type = "error";
    $response->message = "All fields are required.";
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response->type = "error";
    $response->message = "Invalid email address.";
    echo json_encode($response);
    exit;
}

// Check if user already exists
$check = $db->search("SELECT id FROM users WHERE username = ? OR email = ?", "ss", [$username, $email]);
if (count($check) > 0) {
    $response->type = "error";
    $response->message = "Username or email already exists.";
    echo json_encode($response);
    exit;
}

// Secure password hash
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$register = $db->iud(
    "INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
    "sssss",
    [$username, $email, $hashedPassword, $role, 'ACTIVE']
);

if ($register['affected_rows'] > 0) {
    $response->type = "success";
    $response->message = "User registered successfully.";
    $response->user_id = $register['insert_id'];
} else {
    $response->type = "error";
    $response->message = "Registration failed, try again later.";
}

echo json_encode($response);
