<?php
session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get campus name from the query parameter
$campus = isset($_GET['campus']) ? $_GET['campus'] : '';

if (empty($campus)) {
    // If no campus is selected, redirect to the dashboard
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch the students for the selected campus
$studentsQuery = "SELECT roll_no AS student_id, name, gmail, status, id FROM users WHERE campus = '$campus' AND role = 'Student'";
$studentsResult = mysqli_query($conn, $studentsQuery);

$studentsData = [];
if (mysqli_num_rows($studentsResult) > 0) {
    while ($row = mysqli_fetch_assoc($studentsResult)) {
        $studentsData[] = $row;
    }
}

// Calculate total fees collected for the campus
$feeQuery = "SELECT SUM(amount) AS total_fees FROM fees WHERE student_id IN (SELECT id FROM users WHERE campus = '$campus')";
$feeResult = mysqli_query($conn, $feeQuery);
$feeData = mysqli_fetch_assoc($feeResult);
$totalFees = $feeData['total_fees'] ?? 0;

// Fetch fees based on the selected month
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('F');
$monthFeeQuery = "SELECT SUM(amount) AS monthly_fees FROM fees WHERE student_id IN (SELECT id FROM users WHERE campus = '$campus') AND month = '$selectedMonth'";
$monthFeeResult = mysqli_query($conn, $monthFeeQuery);
$monthFeeData = mysqli_fetch_assoc($monthFeeResult);
$monthlyFees = $monthFeeData['monthly_fees'] ?? 0;

// Calculate total students
$totalStudents = count($studentsData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $campus; ?> Campus Details - Rehan School</title>
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
            z-index: 100;
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
        }
        .details-container {
            max-width: 1200px;
            width: 100%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 28px;
            color: #333;
            text-align: left;
            margin-bottom: 20px;
        }
        .filter-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .filter-section select,
        .filter-section input {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
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
        .details-button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        .details-button:hover {
            background-color: #0056b3;
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
    <div class="details-container">
        <h1><?php echo $campus; ?> Campus - Student Details (Total: <?php echo $totalStudents; ?>)</h1>


        <!-- Summary Section for Total Students, Total Fees, and Monthly Fees -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px;">
            <!-- Card 1: Total Students -->
            <div style="background-color: #007bff; color: white; padding: 10px; width: 30%; text-align: center; border-radius: 8px; min-width: 150px;">
                <h3>Total Students</h3>
                <p style="font-size: 24px;"><?php echo $totalStudents; ?></p>
            </div>

            <!-- Card 2: Total Fees Received -->
            <div style="background-color: #28a745; color: white; padding: 10px; width: 30%; text-align: center; border-radius: 8px; min-width: 150px;">
                <h3>Total Fees Received</h3>
                <p style="font-size: 24px;">PKR <?php echo number_format($totalFees, 0); ?></p>
            </div>

            <!-- Card 3: Fees by Month with Dropdown -->
            <div style="background-color: #ffc107; color: white; padding: 10px; width: 30%; text-align: center; border-radius: 8px; min-width: 150px;">
                <h3>Fees for <?php echo $selectedMonth; ?></h3>
                <p style="font-size: 24px;">PKR <?php echo number_format($monthlyFees, 0); ?></p>
                <form method="get" action="">
                    <input type="hidden" name="campus" value="<?php echo $campus; ?>">
                    <select name="month" onchange="this.form.submit()" style="padding: 5px; border-radius: 5px; border: none; font-size: 16px;">
                        <option value="January" <?php if ($selectedMonth == 'January') echo 'selected'; ?>>January</option>
                        <option value="February" <?php if ($selectedMonth == 'February') echo 'selected'; ?>>February</option>
                        <option value="March" <?php if ($selectedMonth == 'March') echo 'selected'; ?>>March</option>
                        <option value="April" <?php if ($selectedMonth == 'April') echo 'selected'; ?>>April</option>
                        <option value="May" <?php if ($selectedMonth == 'May') echo 'selected'; ?>>May</option>
                        <option value="June" <?php if ($selectedMonth == 'June') echo 'selected'; ?>>June</option>
                        <option value="July" <?php if ($selectedMonth == 'July') echo 'selected'; ?>>July</option>
                        <option value="August" <?php if ($selectedMonth == 'August') echo 'selected'; ?>>August</option>
                        <option value="September" <?php if ($selectedMonth == 'September') echo 'selected'; ?>>September</option>
                        <option value="October" <?php if ($selectedMonth == 'October') echo 'selected'; ?>>October</option>
                        <option value="November" <?php if ($selectedMonth == 'November') echo 'selected'; ?>>November</option>
                        <option value="December" <?php if ($selectedMonth == 'December') echo 'selected'; ?>>December</option>
                    </select>
                </form>
            </div>
        </div>

         <form method="post" action="generate_student_report.php">
    <input type="hidden" name="campus" value="<?php echo $campus; ?>">
    <button type="submit" class="download-report-btn" 
        style="background-color: #007bff; color: white; padding: 10px 20px; 
        font-size: 16px; border: none; border-radius: 8px; cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: background-color 0.3s ease;
        margin-bottom: 20px;">
        Download All Student Report
    </button>
</form>


            <div class="filter-section">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Left">Left</option>
                </select>
                <input type="text" id="searchInput" placeholder="Search by ID, Name, or Email">
            </div>
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($studentsData)): ?>
                        <?php foreach ($studentsData as $student): ?>
                            <tr>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['gmail']; ?></td>
                                <td><?php echo $student['status']; ?></td>
                                <td><a href="student_details.php?id=<?php echo $student['id']; ?>" class="details-button">View Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No students found for this campus.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    
    <script>
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('searchInput').addEventListener('keyup', filterTable);

        function filterTable() {
            var filterStatus = document.getElementById('statusFilter').value.toLowerCase();
            var searchQuery = document.getElementById('searchInput').value.toLowerCase();
            var table = document.getElementById('studentsTable');
            var rows = table.getElementsByTagName('tr');

            for (var i = 1; i < rows.length; i++) {
                var studentId = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                var studentName = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                var studentEmail = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                var studentStatus = rows[i].getElementsByTagName('td')[3].textContent.toLowerCase();

                if (
                    (filterStatus === '' || studentStatus === filterStatus) &&
                    (studentId.indexOf(searchQuery) > -1 || studentName.indexOf(searchQuery) > -1 || studentEmail.indexOf(searchQuery) > -1)
                ) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
        
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