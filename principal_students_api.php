<?php
// Include database connection from db.php
include 'db.php';

// Set headers to return JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Check if user_id is provided
if (isset($_GET['id'])) {
    // Escape the user input to prevent SQL injection
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch principal's information
    $principal_query = "SELECT id, name, campus, picture_path FROM users WHERE id = '$user_id' AND role = 'principal'";
    $principal_result = mysqli_query($conn, $principal_query);

    if (mysqli_num_rows($principal_result) > 0) {
        $principal = mysqli_fetch_assoc($principal_result);
        $campus = $principal['campus'];

        // Fetch students from the principal's campus
        $students_query = "SELECT * FROM users WHERE role = 'student' AND campus = '$campus'";
        $students_result = mysqli_query($conn, $students_query);

        $students = [];
        while ($row = mysqli_fetch_assoc($students_result)) {
            $students[] = $row;
        }

        // Prepare the response with both principal and students data
        $response = [
            'principal' => [
                'id' => $principal['id'],
                'name' => $principal['name'],
                'campus' => $principal['campus'],
                'picture_path' => $principal['picture_path']
            ],
            'students' => $students
        ];

        // Output the response as a JSON object
        echo json_encode($response);
    } else {
        // If no principal is found with the given ID
        echo json_encode(['error' => 'Principal not found']);
    }
} else {
    // If no user ID is provided in the request
    echo json_encode(['error' => 'No user ID provided']);
}

// Close the database connection
mysqli_close($conn);
?>