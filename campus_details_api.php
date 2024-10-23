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

// Get the campus parameter from the request
$campus = isset($_GET['campus']) ? $_GET['campus'] : die(json_encode(['message' => 'Campus not specified.']));

// Initialize response array
$response = array();

// Clean any previous output buffer to avoid unwanted characters
ob_clean();

// Fetch campus-specific student details with the correct query
$studentsQuery = "SELECT roll_no AS student_id, name, gmail, status, id FROM users WHERE campus = '$campus' AND role = 'Student'";
$result = mysqli_query($conn, $studentsQuery);

if (!$result) {
    die(json_encode(['message' => 'Query failed: ' . mysqli_error($conn)]));
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = $row;
    }
} else {
    $response['message'] = "No students found for this campus.";
}

// Echo JSON-encoded response
echo json_encode($response);

// Terminate the script to prevent further output
exit();
?>
