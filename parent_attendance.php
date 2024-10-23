<?php
session_start();
require 'db.php';

// Check if the user is logged in and is a Parent
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Parent') {
    header('Location: login.php');
    exit();
}

$parent_id = $_SESSION['admin_id'];

// Fetch parent's children
$children_query = "SELECT id, name, roll_no, campus FROM users WHERE parent_id = ? AND role = 'Student'";
$stmt = $conn->prepare($children_query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$children_result = $stmt->get_result();

// Handle month and year selection
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Children's Monthly Attendance - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .month-picker {
            margin-bottom: 30px;
            text-align: center;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .month-picker label {
            font-weight: bold;
            margin-right: 10px;
            color: #333;
        }
        .month-picker select {
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            background-color: white;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23333" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            padding-right: 30px;
        }
        .month-picker select:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .month-picker select:hover {
            border-color: #80bdff;
        }
        @media (max-width: 600px) {
            .month-picker select {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        .child-attendance {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .child-attendance h2 {
            color: #007bff;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .present {
            color: green;
        }
        .absent {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Children's Monthly Attendance</h1>
        
        <div class="month-picker">
            <form action="" method="GET">
                <label for="month">Select Month:</label>
                <select id="month" name="month" onchange="this.form.submit()">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $month = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
                        echo "<option value='" . str_pad($m, 2, "0", STR_PAD_LEFT) . "' " . ($selected_month == str_pad($m, 2, "0", STR_PAD_LEFT) ? "selected" : "") . ">$month</option>";
                    }
                    ?>
                </select>
                
                <label for="year">Select Year:</label>
                <select id="year" name="year" onchange="this.form.submit()">
                    <?php
                    $current_year = date('Y');
                    for ($y = $current_year; $y >= $current_year - 5; $y--) {
                        echo "<option value='$y' " . ($selected_year == $y ? "selected" : "") . ">$y</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <?php while ($child = $children_result->fetch_assoc()): ?>
            <div class="child-attendance">
                <h2><?php echo $child['name']; ?> (Roll No: <?php echo $child['roll_no']; ?>)</h2>
                <p>Campus: <?php echo $child['campus']; ?></p>
                
                <?php
                // Fetch attendance for this child for the selected month and year
                $attendance_query = "SELECT date, attended FROM attendance WHERE student_id = ? AND MONTH(date) = ? AND YEAR(date) = ? ORDER BY date";
                $stmt = $conn->prepare($attendance_query);
                $stmt->bind_param("iss", $child['id'], $selected_month, $selected_year);
                $stmt->execute();
                $attendance_result = $stmt->get_result();
                ?>

                <table>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                    <?php while ($attendance = $attendance_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d-m-Y', strtotime($attendance['date'])); ?></td>
                            <td class="<?php echo $attendance['attended'] ? 'present' : 'absent'; ?>">
                                <?php echo $attendance['attended'] ? 'Present' : 'Absent'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
