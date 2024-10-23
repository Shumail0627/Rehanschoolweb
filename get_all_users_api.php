<?php
// Include database connection from db.php
include 'db.php';

// Set headers to return JSON and allow requests from any origin
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Check if student_id is provided
if (isset($_GET['id'])) {
    // Escape the user input to prevent SQL injection
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch individual student's information if an ID is provided
    $student_query = "SELECT * FROM users WHERE id = '$student_id' AND role = 'student'";
    $student_result = mysqli_query($conn, $student_query);

    if (mysqli_num_rows($student_result) > 0) {
        // Student found, fetch data
        $student = mysqli_fetch_assoc($student_result);

        // Prepare the response data for the student
        $response = [
            'student' => [
                'id' => $student['id'],
                'name' => $student['name'],
                'email' => $student['email'],
                'campus' => $student['campus'],
                'picture_path' => $student['picture_path'] ?? null,
                'roll_no' => $student['roll_no'] ?? null,
                'created_at' => $student['created_at'] ?? null, // Date student was added
            ]
        ];

        // Output the response as a JSON object
        echo json_encode($response);
    } else {
        // If no student is found with the given ID
        echo json_encode(['error' => 'Student not found']);
    }
} else {
    // If no ID is provided, fetch all students with role 'student'
    $students_query = "SELECT * FROM users WHERE role = 'student'";
    $students_result = mysqli_query($conn, $students_query);

    $students = [];
    while ($row = mysqli_fetch_assoc($students_result)) {
        $students[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'campus' => $row['campus'],
            'picture_path' => $row['picture_path'] ?? null,
            'roll_no' => $row['roll_no'] ?? null,
            'created_at' => $row['created_at'] ?? null, // Date student was added
        ];
    }

    // Prepare the response data for all students
    $response = [
        'students' => $students
    ];

    // Output the response as a JSON object
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
?>
