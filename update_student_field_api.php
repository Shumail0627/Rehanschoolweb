<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
    $field = mysqli_real_escape_string($conn, $_POST['field']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);

    $allowedFields = ['student_whatsapp', 'introduction', 'favorite_food_dishes', 'biggest_wish', 'vision_10_years', 'ideal_personalities',
    'age','dob','class','roll_no','student_whatsapp','father_name','mother_name','father_job','mother_job',];

    if (in_array($field, $allowedFields)) {
        $query = "UPDATE users SET $field = '$value' WHERE id = '$studentId'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Field updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update field: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "This field cannot be edited"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>