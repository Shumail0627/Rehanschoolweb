<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers to ensure proper JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection
require 'db.php';

// Initialize response array
$response = array();

// Clean any previous output buffer to avoid unwanted characters
ob_clean();

// Fetch all students
$query = "SELECT id, roll_no, name, gmail, status, campus FROM users WHERE role = 'Student'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]));
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = [
            'id' => $row['id'],
            'user_id' => $row['roll_no'],
            'name' => $row['name'],
            'email' => $row['gmail'],
            'status' => $row['status'],
            'campus' => $row['campus']
        ];
    }
} else {
    $response['error'] = "No students found.";
}

// Echo JSON-encoded response
echo json_encode($response);

// Close the connection
mysqli_close($conn);

// Terminate the script to prevent further output
exit();
?>