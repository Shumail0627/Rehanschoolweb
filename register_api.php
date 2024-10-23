<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the request is JSON or form data
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if ($contentType === "application/json") {
        $data = json_decode(file_get_contents("php://input"));
    } else {
        $data = (object) $_POST;
    }

    // Validate required fields
    if (
        !empty($data->name) &&
        !empty($data->email) &&
        !empty($data->password)
    ) {
        $name = mysqli_real_escape_string($conn, $data->name);
        $email = mysqli_real_escape_string($conn, $data->email);
        $password = mysqli_real_escape_string($conn, $data->password);
        $campus = !empty($data->campus) ? mysqli_real_escape_string($conn, $data->campus) : 'Not Specified';

        // Check if the email is already registered
        $check_email = "SELECT * FROM users WHERE gmail = '$email'";
        $result = mysqli_query($conn, $check_email);

        if (mysqli_num_rows($result) > 0) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Email is already registered."));
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $query = "INSERT INTO users (name, gmail, password, campus, role) VALUES ('$name', '$email', '$hashed_password', '$campus', 'Student')";

            if (mysqli_query($conn, $query)) {
                http_response_code(201);
                echo json_encode(array("status" => "success", "message" => "User registered successfully."));
            } else {
                http_response_code(500);
                echo json_encode(array("status" => "error", "message" => "Unable to register the user. Database error."));
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Unable to register the user. Required data is missing."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("status" => "error", "message" => "Method not allowed. Use POST request."));
}

mysqli_close($conn);
?>