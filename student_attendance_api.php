<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'db.php';

if (isset($_GET['id'])) {
    $studentId = mysqli_real_escape_string($conn, $_GET['id']);
    
    $query = "SELECT date, attended FROM attendance WHERE student_id = '$studentId' ORDER BY date DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $attendanceRecords = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $attendanceRecords[] = $row;
        }
        echo json_encode($attendanceRecords);
    } else {
        echo json_encode(["error" => "Failed to fetch attendance records"]);
    }
} else {
    echo json_encode(["error" => "No student ID provided"]);
}
?>