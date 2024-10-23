<?php
session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get student ID from the query parameter
$studentId = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($studentId)) {
    // If no student ID is provided, redirect to the dashboard
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch the student's details
$studentQuery = "SELECT * FROM users WHERE id = '$studentId' AND role = 'Student'";
$studentResult = mysqli_query($conn, $studentQuery);

if (mysqli_num_rows($studentResult) == 1) {
    $student = mysqli_fetch_assoc($studentResult);
} else {
    // If no student is found, redirect to the dashboard
    header('Location: admin_dashboard.php');
    exit();
}

// Handle attendance form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attended'])) {
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $attended = mysqli_real_escape_string($conn, $_POST['attended']);

    // Insert or update the attendance record for the given student and date
    $attendanceQuery = "INSERT INTO attendance (student_id, date, attended) VALUES ('$studentId', '$date', '$attended')
                        ON DUPLICATE KEY UPDATE attended = '$attended'";
    if (mysqli_query($conn, $attendanceQuery)) {
        $success_message = "Attendance marked successfully.";
    } else {
        $error_message = "Error marking attendance: " . mysqli_error($conn);
    }
}

// Handle fee update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['monthly_fee'])) {
    $monthly_fee = mysqli_real_escape_string($conn, $_POST['monthly_fee']);

    // Update the monthly fee for the student in the database
    $feeUpdateQuery = "UPDATE users SET monthly_fee = '$monthly_fee' WHERE id = '$studentId'";
    if (mysqli_query($conn, $feeUpdateQuery)) {
        $fee_success_message = "Monthly fee updated successfully.";

        // Update the student array to reflect the new fee in case the page is not reloaded
        $student['monthly_fee'] = $monthly_fee;
    } else {
        $fee_error_message = "Error updating fee: " . mysqli_error($conn);
    }
}

// Fetch the student's attendance details
$attendanceQuery = "SELECT COUNT(*) AS total_days, SUM(attended) AS attended_days FROM attendance WHERE student_id = '$studentId'";
$attendanceResult = mysqli_query($conn, $attendanceQuery);
$attendance = mysqli_fetch_assoc($attendanceResult);

$totalDays = $attendance['total_days'];
$attendedDays = $attendance['attended_days'];
$attendancePercentage = $totalDays > 0 ? ($attendedDays / $totalDays) * 100 : 0;
$absentDays = $totalDays - $attendedDays;

// Fetch the student's fee ledger
$feeLedgerQuery = "SELECT month, year, amount, status FROM fees WHERE student_id = '$studentId'";
$feeLedgerResult = mysqli_query($conn, $feeLedgerQuery);

// Calculate total fees received
$totalFeesReceivedQuery = "SELECT SUM(amount) AS total_received FROM fees WHERE student_id = '$studentId' AND status = 'Paid'";
$totalFeesReceivedResult = mysqli_query($conn, $totalFeesReceivedQuery);
$totalFeesReceived = mysqli_fetch_assoc($totalFeesReceivedResult)['total_received'];

// Format the D.O.B and D.O.J to 'd-M-Y' format (e.g., '29-Aug-2024')
$formattedDob = (!empty($student['dob']) && strtotime($student['dob']) > 0) ? date('d-M-Y', strtotime($student['dob'])) : 'Not Available';
$formattedDoj = (!empty($student['doa']) && strtotime($student['doa']) > 0) ? date('d-M-Y', strtotime($student['doa'])) : 'Not Available';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 15px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #fff;
        }

        .user-info {
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 0;
            font-size: 14px;
            color: white;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            width: 90%;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        /* Sidebar Menu */
        .sidebar-menu {
            list-style-type: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            position: relative;
        }

        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background-color: #007bff;
        }

        /* Submenu styling */
        .sidebar-menu .has-submenu ul {
            display: none;
            list-style: none;
            padding-left: 20px;
            opacity: 0;
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .sidebar-menu .has-submenu.open ul {
            display: block;
            opacity: 1;
            max-height: 200px; /* Adjust based on submenu height */
        }

        /* Dropdown Arrow */
        .sidebar-menu .has-submenu > a::after {
            content: '\25BC'; /* Down arrow */
            position: absolute;
            right: 15px;
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .sidebar-menu .has-submenu.open > a::after {
            transform: rotate(-180deg); /* Rotate arrow when submenu is open */
        }

        .submenu li a {
            font-size: 14px;
        }

        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .details-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 65%; /* Changed to 65% */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .attendance-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 35%; /* Changed to 35% */
        }

        h1, h2 {
            font-size: 28px;
            color: #333;
            text-align: left;
            margin-bottom: 20px;
        }

        .student-detail {
            margin-bottom: 15px;
            text-align: justify;
        }

        .student-detail strong {
            display: inline-block;
            width: 250px;
            color: #555;
        }

        .student-picture img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 3px solid #ffc300;
            padding: 5px;
        }

        .details-container h1 {
            margin-bottom: 30px;
            font-size: 26px;
            color: #495057;
        }

        .details-container .student-detail {
            margin-bottom: 20px;
        }

        .details-container .student-detail strong {
            font-weight: 600;
            color: #495057;
        }

        .details-container .student-detail span {
            font-weight: 400;
            color: #6c757d;
        }

        .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            text-align: center;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .attendance-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .attendance-progress {
            width: 100%;
            background-color: #f4f4f4;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .attendance-progress-bar {
            height: 30px;
            background-color: #28a745;
            width: <?php echo $attendancePercentage; ?>%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .attendance-form {
            margin-top: 20px;
        }

        .attendance-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .attendance-form input, .attendance-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .attendance-form button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .attendance-form button:hover {
            background-color: #0056b3;
        }

        canvas {
            width: 250px !important;  /* Adjust the width to make the chart smaller */
            height: 250px !important; /* Adjust the height to make the chart smaller */
            max-width: 100%;
            margin: 0 auto;
        }

        .fee-container, .fee-ledger {
            margin-top: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .fee-form label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .fee-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .fee-form button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .fee-form button:hover {
            background-color: #0056b3;
        }

        .fee-ledger table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .fee-ledger table, .fee-ledger th, .fee-ledger td {
            border: 1px solid #ddd;
        }

        .fee-ledger th, .fee-ledger td {
            padding: 10px;
            text-align: left;
        }

        .fee-ledger th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2># Rehan School</h2>
        <div class="user-info">
            <p><?php echo $_SESSION['admin_name']; ?></p>
            <p><?php echo $_SESSION['role']; ?></p>
        </div>
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search..." required>
            </form>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
            <li class="has-submenu">
                <a href="#">Users</a>
                <ul class="submenu">
                    <li><a href="add_user.php">Add User</a></li>
                    <li><a href="manage_users.php">Manage Users</a></li>
                </ul>
            </li>
            <li><a href="students.php">Students</a></li>
            <li class="has-submenu">
                <a href="#">Student Fees</a>
                <ul class="submenu">
                    <li><a href="add_student_fee.php">Add Student Fee</a></li>
                    <li><a href="show_student_fees.php">Show Student Fees</a></li>
                    <li><a href="manage_fees.php">Generate Voucher PDF</a></li>
                </ul>
            </li>
            <li><a href="attendance.php">Attendance</a></li>
            <li><a href="online_payments.php">Online Payments</a></li>
            <li><a href="staff_info.php">Staff Information</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="top-section">
            <div class="details-container">
                <h1>Student Details</h1>
                <div class="student-picture">
                    <img src="<?php echo $student['picture_path']; ?>" alt="Student Picture">
                </div>
                <div class="student-detail text-justify"><strong>Student ID:</strong> <span><?php echo $student['roll_no']; ?></span></div>
                <div class="student-detail"><strong>Name:</strong> <span><?php echo $student['name']; ?></span></div>
                <div class="student-detail"><strong>Email:</strong> <span><?php echo $student['gmail']; ?></span></div>
                <div class="student-detail"><strong>Date of Birth (D.O.B):</strong> <span><?php echo $formattedDob; ?></span></div>
                <div class="student-detail"><strong>Date of Joining (D.O.J):</strong> <span><?php echo $formattedDoj; ?></span></div>
                <div class="student-detail"><strong>Father's Name:</strong> <span><?php echo $student['father_name']; ?></span></div>
                <div class="student-detail"><strong>Father's Age:</strong> <span><?php echo $student['father_age']; ?></span></div>
                <div class="student-detail"><strong>Father's Job:</strong> <span><?php echo $student['father_job']; ?></span></div>
                <div class="student-detail"><strong>Father's WhatsApp:</strong> <span><?php echo $student['father_whatsapp']; ?></span></div>
                <div class="student-detail"><strong>Mother's Name:</strong> <span><?php echo $student['mother_name']; ?></span></div>
                <div class="student-detail"><strong>Mother's Age:</strong> <span><?php echo $student['mother_age']; ?></span></div>
                <div class="student-detail"><strong>Mother's Job:</strong> <span><?php echo $student['mother_job']; ?></span></div>
                <div class="student-detail"><strong>Mother's WhatsApp:</strong> <span><?php echo $student['mother_whatsapp']; ?></span></div>
                <div class="student-detail"><strong>Number of Siblings:</strong> <span><?php echo $student['number_of_siblings']; ?></span></div>
                <div class="student-detail"><strong>Age:</strong> <span><?php echo $student['child_age']; ?> years</span></div>
                <div class="student-detail"><strong>Class:</strong> <span><?php echo $student['class']; ?></span></div>
                <div class="student-detail"><strong>Campus:</strong> <span><?php echo $student['campus']; ?></span></div>
                <div class="student-detail"><strong>City:</strong> <span><?php echo $student['city']; ?></span></div>
                <div class="student-detail"><strong>Country:</strong> <span><?php echo $student['country']; ?></span></div>
                <div class="student-detail"><strong>Reason for Joining:</strong> <span><?php echo $student['reason_for_joining']; ?></span></div>
                <div class="student-detail"><strong>Favorite Food Dishes:</strong> <span><?php echo $student['favorite_food_dishes']; ?></span></div>
                <div class="student-detail"><strong>Ideal Personalities:</strong> <span><?php echo $student['ideal_personalities']; ?></span></div>
                <div class="student-detail"><strong>Plan if given 1 crore rupees:</strong> <span><?php echo $student['plan_for_crore_rupees']; ?></span></div>
                <div class="student-detail"><strong>Biggest Wish:</strong> <span><?php echo $student['biggest_wish']; ?></span></div>
                <div class="student-detail"><strong>Vision for 10 Years Ahead:</strong> <span><?php echo $student['vision_10_years']; ?></span></div>
                <div class="student-detail"><strong>Student's WhatsApp:</strong> <span><?php echo $student['student_whatsapp']; ?></span></div>
                <a href="campus_details.php?campus=<?php echo $student['campus']; ?>" class="back-button">Back to Campus Details</a>
                <a href="edit_student.php?id=<?php echo $studentId; ?>" class="back-button" style="background-color: #28a745; margin-left: 10px;">Edit</a>
            </div>
            
            <div class="attendance-container">
                <h2 class="attendance-title">Attendance Analytics</h2>
                <canvas id="attendanceChart"></canvas>
                <p>Total Days: <?php echo $totalDays; ?></p>
                <p>Attended Days: <?php echo $attendedDays; ?></p>

                <!-- Attendance Form -->
                <div class="attendance-form">
                    <?php if (isset($success_message)) { echo "<p style='color:green;'>$success_message</p>"; } ?>
                    <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
                    <form method="POST" action="">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>

                        <label for="attended">Attendance:</label>
                        <select id="attended" name="attended">
                            <option value="1">Present</option>
                            <option value="0">Absent</option>
                        </select>

                        <button type="submit">Mark Attendance</button>
                    </form>
                </div>
                <div class="attendance-form">
                    <a href="view_attendance.php?id=<?php echo $studentId; ?>" class="back-button">View Attendance Details</a>
                </div>
            </div>
        </div>

         <div class="fee-container">
            <h2>Monthly Fee</h2>
            <form method="POST" class="fee-form">
                <label for="monthly_fee">Monthly Fee (PKR):</label>
                <input type="number" name="monthly_fee" id="monthly_fee" value="<?php echo $student['monthly_fee']; ?>" required>
                <button type="submit">Update Fee</button>
            </form>
            <?php if (isset($fee_success_message)) { echo "<p style='color:green;'>$fee_success_message</p>"; } ?>
            <?php if (isset($fee_error_message)) { echo "<p style='color:red;'>$fee_error_message</p>"; } ?>
            <p>Total Fees Received: PKR <?php echo number_format($totalFeesReceived, 2); ?></p>
        </div>
        
        <div class="fee-ledger">
            <h2>Fee Ledger</h2>
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($feeLedgerResult)) { ?>
                    <tr>
                        <td><?php echo $row['month']; ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td>PKR <?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Attended Days', 'Absent Days'],
            datasets: [{
                data: [<?php echo $attendedDays; ?>, <?php echo $absentDays; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderColor: ['#28a745', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        var submenuLinks = document.querySelectorAll('.has-submenu > a');
        
        submenuLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent the default link behavior
                var parentLi = this.parentElement;
                parentLi.classList.toggle('open'); // Toggle the 'open' class
            });
        });
    });
    </script>
</body>
</html>
