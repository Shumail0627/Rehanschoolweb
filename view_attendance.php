<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Determine the role of the logged-in user
if (isset($_SESSION['admin_id'])) {
    $userId = $_SESSION['admin_id'];
    $userRole = 'Admin';
} elseif (isset($_SESSION['student_id'])) {
    $userId = $_SESSION['student_id'];
    $userRole = 'Student';
} else {
    die("Error: Unauthorized access.");
}

// Determine which student's attendance to display
if ($userRole == 'Admin' || $userRole == 'Principal') {
    $studentId = isset($_GET['id']) ? intval($_GET['id']) : null;
    if ($studentId === null) {
        die("Error: No student selected. Please go back and select a student.");
    }
} else {
    // If the user is a student, they can only view their own attendance
    $studentId = $userId;
}

// Set default month and year to current month and year
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : 'all';
$selectedYear = isset($_POST['year']) ? $_POST['year'] : 'all';

// Fetch the student's attendance details based on the selected month and year
if ($selectedMonth == 'all' && $selectedYear == 'all') {
    $attendanceQuery = "SELECT date, attended FROM attendance WHERE student_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param("i", $studentId);
} elseif ($selectedMonth == 'all') {
    $attendanceQuery = "SELECT date, attended FROM attendance WHERE student_id = ? AND YEAR(date) = ? ORDER BY date DESC";
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param("ii", $studentId, $selectedYear);
} elseif ($selectedYear == 'all') {
    $attendanceQuery = "SELECT date, attended FROM attendance WHERE student_id = ? AND MONTH(date) = ? ORDER BY date DESC";
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param("ii", $studentId, $selectedMonth);
} else {
    $attendanceQuery = "SELECT date, attended FROM attendance WHERE student_id = ? AND MONTH(date) = ? AND YEAR(date) = ? ORDER BY date DESC";
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param("iii", $studentId, $selectedMonth, $selectedYear);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch student name
$nameQuery = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($nameQuery);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$nameResult = $stmt->get_result();
$studentName = $nameResult->fetch_assoc()['name'];

// Generate month and year dropdown options
$months = range(1, 12);
$years = range(date('Y'), date('Y') - 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Details - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .details-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 900px;
        }
        h1 {
            font-size: 28px;
            color: #333;
            text-align: left;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .status-present {
            color: #28a745;
            font-weight: bold;
        }
        .status-absent {
            color: #dc3545;
            font-weight: bold;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form select {
            padding: 10px;
            margin-right: 10px;
            font-size: 16px;
        }
        .filter-form button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="details-container">
        <h1>Attendance Details for <?php echo htmlspecialchars($studentName); ?></h1>

        <form class="filter-form" method="POST" action="">
            <select name="month" required>
                <option value="all" <?php echo $selectedMonth == 'all' ? 'selected' : ''; ?>>All Months</option>
                <?php foreach ($months as $month): ?>
                    <option value="<?php echo $month; ?>" <?php echo $month == $selectedMonth ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="year" required>
                <option value="all" <?php echo $selectedYear == 'all' ? 'selected' : ''; ?>>All Years</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filter</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($row['date'])); ?></td>
                        <td class="<?php echo $row['attended'] ? 'status-present' : 'status-absent'; ?>">
                            <?php echo $row['attended'] ? 'Present' : 'Absent'; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No attendance records found for the selected period.</p>
        <?php endif; ?>

        <a href="<?php echo strtolower($userRole); ?>_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
