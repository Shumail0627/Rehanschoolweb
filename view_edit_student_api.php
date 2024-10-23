<?php
include 'db_connection.php'; // Make sure you include your DB connection file

// Check request method
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];

        $sql = "SELECT * FROM students WHERE student_id = '$student_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            echo json_encode($student);
        } else {
            echo json_encode(["message" => "Student not found"]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Updating student data
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $class = $_POST['class'];

    $sql = "UPDATE students SET name='$name', email='$email', class='$class' WHERE student_id='$student_id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Student updated successfully"]);
    } else {
        echo json_encode(["message" => "Error updating student: " . $conn->error]);
    }
} else {
    echo json_encode(["message" => "Invalid request"]);
}
?>
