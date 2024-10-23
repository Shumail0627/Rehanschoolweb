<?php
session_start();
require 'db.php'; // Include the database connection

// Check if user is logged in as a student
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$studentId = $_SESSION['student_id'];

// Get the selected month and year from the form (default to all months and all years)
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : 'All Months';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : 'All Years';

// Define the months array
$months = range(1, 12);

// Define the years array (you can customize the range as needed)
$years = range(date('Y') - 10, date('Y')); // Last 10 years

// Build the query based on the selected filters
$attendanceQuery = "SELECT date, attended FROM attendance WHERE student_id = ?";
if ($selectedMonth !== 'All Months') {
    $attendanceQuery .= " AND MONTH(date) = " . intval($selectedMonth);
}
if ($selectedYear !== 'All Years') {
    $attendanceQuery .= " AND YEAR(date) = " . intval($selectedYear);
}
$attendanceQuery .= " ORDER BY date DESC";

$stmt = $conn->prepare($attendanceQuery);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch student name
$nameQuery = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($nameQuery);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$nameResult = $stmt->get_result();
$studentName = $nameResult->fetch_assoc()['name'];
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

        <form class="filter-form" method="GET" action="">
            <select name="month" required>
                <option value="All Months" <?php echo $selectedMonth == 'All Months' ? 'selected' : ''; ?>>All Months</option>
                <?php foreach ($months as $month): ?>
                    <option value="<?php echo $month; ?>" <?php echo $month == $selectedMonth ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="year" required>
                <option value="All Years" <?php echo $selectedYear == 'All Years' ? 'selected' : ''; ?>>All Years</option>
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
                        <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                        <td class="<?php echo $row['attended'] ? 'status-present' : 'status-absent'; ?>">
                            <?php echo $row['attended'] ? 'Present' : 'Absent'; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No attendance records found for the selected period.</p>
        <?php endif; ?>

        <a href="student_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
