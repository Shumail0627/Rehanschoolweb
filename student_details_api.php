<?php
header('Content-Type: application/json');
require 'db.php'; // Database connection file

// Check if student ID is provided
$studentId = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($studentId)) {
    echo json_encode(['error' => 'No student ID provided']);
    exit();
}

// Fetch student details using prepared statements
$studentQuery = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'Student'");
$studentQuery->bind_param('s', $studentId);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

if ($studentResult->num_rows == 1) {
    $student = $studentResult->fetch_assoc();
    
    // Format dates
    $student['dob'] = (!empty($student['dob']) && strtotime($student['dob']) > 0) ? date('d-M-Y', strtotime($student['dob'])) : 'Not Available';
    $student['doa'] = (!empty($student['doa']) && strtotime($student['doa']) > 0) ? date('d-M-Y', strtotime($student['doa'])) : 'Not Available';

    // Fetch attendance details using prepared statements
    $attendanceQuery = $conn->prepare("SELECT COUNT(*) AS total_days, SUM(attended) AS attended_days FROM attendance WHERE student_id = ?");
    $attendanceQuery->bind_param('s', $studentId);
    $attendanceQuery->execute();
    $attendanceResult = $attendanceQuery->get_result();
    $attendance = $attendanceResult->fetch_assoc();

    $totalDays = $attendance['total_days'] ?? 0;
    $attendedDays = $attendance['attended_days'] ?? 0;
    $attendancePercentage = $totalDays > 0 ? ($attendedDays / $totalDays) * 100 : 0;

    // Fetch fee details using prepared statements
    $feeLedgerQuery = $conn->prepare("SELECT month, year, amount, status FROM fees WHERE student_id = ?");
    $feeLedgerQuery->bind_param('s', $studentId);
    $feeLedgerQuery->execute();
    $feeLedgerResult = $feeLedgerQuery->get_result();
    $feeLedger = [];
    while ($row = $feeLedgerResult->fetch_assoc()) {
        $feeLedger[] = $row;
    }

    // Fetch total fees received using prepared statements
    $totalFeesReceivedQuery = $conn->prepare("SELECT SUM(amount) AS total_received FROM fees WHERE student_id = ? AND status = 'Paid'");
    $totalFeesReceivedQuery->bind_param('s', $studentId);
    $totalFeesReceivedQuery->execute();
    $totalFeesReceivedResult = $totalFeesReceivedQuery->get_result();
    $totalFeesReceived = $totalFeesReceivedResult->fetch_assoc()['total_received'] ?? 0;

    // Prepare response
    $response = [
        'student' => $student,
        'attendance' => [
            'total_days' => $totalDays,
            'attended_days' => $attendedDays,
            'attendance_percentage' => round($attendancePercentage, 2)
        ],
        'fees' => [
            'monthly_fee' => $student['monthly_fee'],
            'total_fees_received' => $totalFeesReceived,
            'fee_ledger' => $feeLedger
        ]
    ];

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Student not found']);
}

// Close connections
$studentQuery->close();
$attendanceQuery->close();
$feeLedgerQuery->close();
$totalFeesReceivedQuery->close();
$conn->close();
?>
