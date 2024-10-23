<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php';

$response = array();

if (isset($_POST['token'])) {
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    
    $query = "SELECT * FROM users WHERE auth_token = '$token' AND token_expiry > NOW()";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $response['status'] = 'success';
        $response['user_id'] = $user['id'];
        $response['role'] = $user['role'];
        $response['campus'] = $user['campus'];
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid or expired token';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Token is required';
}

echo json_encode($response);
?>