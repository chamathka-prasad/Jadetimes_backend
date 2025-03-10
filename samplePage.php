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


    //     $sessionAdmin = $_SESSION["rb_admin"];


    $cname = $_POST["cname"];
    $email = $_POST["email"];
    $mobile = $_POST["mobile"];
    $caddress = $_POST["caddress"];


    $regex = '/^\+?\d+$/';
    $message = new stdClass();

    if (empty($cname)) {
        $message->type = "error";
        $message->message = "first name is empty";
        echo json_encode($message);
    } else if (empty($email)) {
        $message->type = "error";
        $message->message = "last name is empty";
        echo json_encode($message);
    } else {


        $sql = "SELECT * FROM manifacturer WHERE name = ?  OR email = ?";
        $result = $db->search($sql, 'ss', [$cname, $email]);


        if (count($result) == 0) {
            $savePath = "";
            if (isset($_FILES["cimg"])) {



                $img = $_FILES["cimg"];


                $ext = pathinfo($img["name"], PATHINFO_EXTENSION);
                $img["type"];
                $fileName = uniqid();
                $savePath = $fileName . "." . $ext;
                $path = "../../../resources/companyImg/" . $fileName . "." . $ext;
                move_uploaded_file($img["tmp_name"], $path);
            }
            $password = uniqid();
            $insertManifacturer = $db->iud("INSERT INTO `manifacturer`(`name`,`address`,`img`,`email`,`password`,`mobile`,`status`)VALUES(?,?,?,?,?,?,?)", "sssssss", [$cname, $caddress, $savePath, $email, $password, $mobile, 'ACTIVE']);

            if ($insertManifacturer['affected_rows'] > 0) {

                $body = '';

                MailSender::sendMail($email, "System Registration", $body);

                $message->type = "success";
                $message->message = "Manufacturer Register Success";
                echo json_encode($message);
            } else {
                $message->type = "error";
                $message->message = "Insert Error";
                echo json_encode($message);
            }
        } else {
            $manifacurer = $result[0];

            $message->type = "error";

            if ($manifacurer["name"] == $cname) {
                $message->message = "Company Name Is Already Registred.";
            } else {
                $message->message = "Company Email Is Already Registred.";
            }

            echo json_encode($message);
        }



    }
}
