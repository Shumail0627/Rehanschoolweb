<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment = $_POST['assignment'];
    $link = $_POST['link'];
    $student_id = $_SESSION['student_id']; // Assuming student ID is stored in session

    // Insert the assignment and link into the database
    $query = "INSERT INTO student_assignments (student_id, assignment, link) VALUES ('$student_id', '$assignment', '$link')";
    
    if (mysqli_query($conn, $query)) {
        echo "Link saved successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
