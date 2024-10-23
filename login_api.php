<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php';

$response = array();

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE gmail = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $response['status'] = 'success';
            $response['message'] = 'Login successful';
            $response['role'] = $user['role'];
            $response['user_id'] = $user['id'];
            $response['token'] = $user['id']; // Use user ID as token
            $response['campus'] = $user['campus']; // Assuming 'campus' field exists
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Incorrect password.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No user found with this email.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Email and Password are required.';
}

echo json_encode($response);
?>