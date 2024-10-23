<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
    $facebook = mysqli_real_escape_string($conn, $_POST['facebook']);
    $linkedin = mysqli_real_escape_string($conn, $_POST['linkedin']);
    $youtube = mysqli_real_escape_string($conn, $_POST['youtube']);
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);

    $query = "UPDATE users SET 
              facebook_link = '$facebook',
              linkedin_link = '$linkedin',
              youtube_link = '$youtube',
              instagram_link = '$instagram'
              WHERE id = '$studentId'";

    $result = mysqli_query($conn, $query);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Social links updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update social links: " . mysqli_error($conn)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>