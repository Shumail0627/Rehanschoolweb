<?php
include 'db.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendance_date = $_POST['date'];
    $campus = $_POST['campus'];
    $attendance_data = json_decode($_POST['attendance'], true);

    if (!$attendance_date || !$campus || !$attendance_data) {
        echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
        exit;
    }

    $success_count = 0;
    $error_messages = [];

    foreach ($attendance_data as $student_id => $is_present) {
        $status = $is_present ? 'Present' : 'Absent';

        $sql = "INSERT INTO attendance (student_id, date, status, campus) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
            exit;
        }

        $stmt->bind_param("ssss", $student_id, $attendance_date, $status, $campus);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_messages[] = "Error for student $student_id: " . $stmt->error;
        }
        $stmt->close();
    }

    if (count($error_messages) == 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Attendance marked successfully for $success_count students"
        ]);
    } else {
        echo json_encode([
            "status" => "partial_success",
            "message" => "Marked attendance for $success_count students. Errors occurred for " . count($error_messages) . " students.",
            "errors" => $error_messages
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>