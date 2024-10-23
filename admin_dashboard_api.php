<?php
// Headers to allow cross-origin requests and to specify the content type as JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
require 'db.php';

// Initialize response array
$response = array();

// Fetch total counts for each role
$roles = ['Student', 'Teacher', 'Principal'];
foreach ($roles as $role) {
    $query = "SELECT COUNT(id) as total FROM users WHERE role = '$role'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $response['total_' . strtolower($role) . 's'] = $data['total'];
}

// Fetch campus-wise breakdown of students, teachers, and total fees
$campuses = ['Korangi', 'Munawwar', 'Islamabad', 'Online'];
foreach ($campuses as $campus) {
    $campusData = array();
    
    // Fetch student and teacher counts per campus
    foreach ($roles as $role) {
        $query = "SELECT COUNT(id) as total FROM users WHERE role = '$role' AND campus = '$campus'";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);
        $campusData[strtolower($role) . '_count'] = $data['total'];
    }

    // Fetch total fees per campus
    $query = "SELECT SUM(amount) as total_fees FROM fees WHERE student_id IN (SELECT id FROM users WHERE campus = '$campus')";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $campusData['total_fees'] = $data['total_fees'] ?? 0; // Handle null values
    
    // Add campus data to response
    $response[$campus . '_campus'] = $campusData;
}

// Return the response as JSON
echo json_encode($response);
?>
