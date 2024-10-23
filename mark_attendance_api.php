<?php
include 'db.php'; // Include the DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $attendance_date = $_POST['attendance_date'];  // Format: Y-m-d
    $status = $_POST['status'];  // Either 'Present' or 'Absent'

    $sql = "INSERT INTO attendance (student_id, date, status) VALUES ('$student_id', '$attendance_date', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Attendance marked successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
} else {
    echo json_encode(["message" => "Invalid request"]);
}
?>
