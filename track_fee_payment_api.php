<?php
include 'db_connection.php'; // Include the DB connection

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    $sql = "SELECT * FROM fees WHERE student_id = '$student_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $fees = [];

        while ($row = $result->fetch_assoc()) {
            $fees[] = $row;
        }

        echo json_encode($fees);
    } else {
        echo json_encode(["message" => "No fees found"]);
    }
} else {
    echo json_encode(["message" => "Invalid request"]);
}
?>
