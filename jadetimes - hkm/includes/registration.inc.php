<?php
session_start();
require_once "../config/MySQLConnector.php"; // Adjust if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $pwd = $_POST["pwd"] ?? '';
    $role = trim($_POST["role"] ?? '');

    // Basic validation
    if (empty($username) || empty($email) || empty($pwd) || empty($role)) {
        $_SESSION["reg_error"] = "All fields are required.";
        header("Location: ../user_registration.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["reg_error"] = "Invalid email format.";
        header("Location: ../user_registration.php");
        exit();
    }

    try {
        $db = new MySQLConnector();

        // Check for existing email or username
        $existing = $db->search("SELECT id FROM users WHERE username = :username OR email = :email", [
            ":username" => $username,
            ":email" => $email
        ]);

        if (!empty($existing)) {
            $_SESSION["reg_error"] = "Username or email already exists.";
            header("Location: ../user_registration.php");
            exit();
        }

        // Secure password hash
        $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);

        // Insert user
        $db->insert("INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)", [
            ":username" => $username,
            ":email" => $email,
            ":password_hash" => $hashed_pwd,
            ":role" => $role
        ]);

        $_SESSION["reg_success"] = "User registered successfully.";
        header("Location: ../user_registration.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION["reg_error"] = "Database error: " . $e->getMessage();
        header("Location: ../user_registration.php");
        exit();
    }

} else {
    header("Location: ../user_registration.php");
    exit();
}
