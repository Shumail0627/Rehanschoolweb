<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require 'db.php';

// Set headers for JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Check if all required fields are present
$required_fields = ['id', 'name', 'roll_no', 'class', 'campus', 'age', 'dob', 'doj', 'student_whatsapp', 'city', 'country', 'father_name', 'mother_name', 'father_job', 'mother_job', 'favorite_food_dishes', 'biggest_wish', 'vision_10_years', 'ideal_personalities'];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Escape all values to prevent SQL injection
foreach ($data as $key => $value) {
    $data[$key] = mysqli_real_escape_string($conn, $value);
}

// Construct the UPDATE query
$query = "UPDATE users SET 
    name = '{$data['name']}',
    roll_no = '{$data['roll_no']}',
    class = '{$data['class']}',
    campus = '{$data['campus']}',
    age = '{$data['age']}',
    dob = '{$data['dob']}',
    doj = '{$data['doj']}',
    student_whatsapp = '{$data['student_whatsapp']}',
    city = '{$data['city']}',
    country = '{$data['country']}',
    father_name = '{$data['father_name']}',
    mother_name = '{$data['mother_name']}',
    father_job = '{$data['father_job']}',
    mother_job = '{$data['mother_job']}',
    favorite_food_dishes = '{$data['favorite_food_dishes']}',
    biggest_wish = '{$data['biggest_wish']}',
    vision_10_years = '{$data['vision_10_years']}',
    ideal_personalities = '{$data['ideal_personalities']}'
WHERE id = '{$data['id']}' AND role = 'student'";

// Execute the query
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Student data updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error updating student data: ' . mysqli_error($conn)]);
}

// Close the database connection
mysqli_close($conn);
?>