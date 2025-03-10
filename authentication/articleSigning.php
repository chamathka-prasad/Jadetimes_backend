<?php
require "../config/MySQLConnector.php";

$body = file_get_contents('php://input'); // get the request data body

$send = json_decode($body); //convert the JSON to Php object

$email = $send->email; //access the php object data;
$password = $send->password;
$rememberPassword = $send->rememberPassword;

$message = new stdClass(); // creata a php object

$db = new MySQLConnector(); //create the db connecter object


$results = $db->search("SELECT * FROM `users` WHERE `email`=? AND `password_hash`=?", "ss", [$email, $password]);
if (count($results)) {
    $userDetails = $result[0];


    if ($userDetails["status"] == 'DEACTIVE') {
        $message->type = "error";
        $message->message = "Incorrect username or password";
        echo json_encode($message);
    } else if ($userDetails["status"] == 'ACTIVE') {
        session_start();
        // create the session
        
        $_SESSION["jade_user"] = $userDetails;
        $message->type = "success";
        $message->message = "correct username and password";
        echo json_encode($message);

        // set the email and password cookies

        if ($rememberPassword == "true") {

            setcookie("email_user", $email, time() + (60 * 60 * 24 * 15));
            setcookie("password_user", $password, time() + (60 * 60 * 24 * 15));
        }
    } else {
        $message->type = "error";
        $message->message = "Invalid Request";
        echo json_encode($message);
    }
} else {

    $message->type = "error";
    $message->message = "Incorrect username or password";
    echo json_encode($message);
}
