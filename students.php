<?php
session_start();
require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all students by default
$query = "SELECT * FROM users WHERE role = 'Student'";
$result = mysqli_query($conn, $query);

$totalStudents = mysqli_num_rows($result);


// Fetch all unique campuses
$campusQuery = "SELECT DISTINCT campus FROM users WHERE role = 'Student'";
$campusResult = mysqli_query($conn, $campusQuery);
$campuses = [];
while ($row = mysqli_fetch_assoc($campusResult)) {
    $campuses[] = $row['campus'];
}

// Fetch all unique statuses
$statusQuery = "SELECT DISTINCT status FROM users WHERE role = 'Student'";
$statusResult = mysqli_query($conn, $statusQuery);
$statuses = [];
while ($row = mysqli_fetch_assoc($statusResult)) {
    $statuses[] = $row['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Rehan School</title>
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
            max-height: 200px;
        }
        .sidebar-menu .has-submenu > a::after {
            content: '\25BC';
            position: absolute;
            right: 15px;
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        .sidebar-menu .has-submenu.open > a::after {
            transform: rotate(-180deg);
        }
        .submenu li a {
            font-size: 14px;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }
        .dashboard-container {
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
        .filters {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .filters select, .filters input[type="text"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-right: 10px;
            margin-bottom: 10px;
            flex-grow: 1;
            min-width: 150px;
        }
        .filters input[type="text"] {
            width: 250px;
            flex-grow: 2;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #ddd;
        }
        a.view-details, a.download-report {
            display: inline-block;
            padding: 6px 10px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 5px;
            width: 120px;
            text-align: center;
        }
        a.view-details {
            background-color: #007bff;
        }
        a.view-details:hover {
            background-color: #0056b3;
        }
        a.download-report {
            background-color: #28a745;
        }
        a.download-report:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
                width: 100%;
            }
            .filters {
                flex-direction: column;
            }
            .filters input[type="text"] {
                width: 100%;
            }
            table, th, td {
                font-size: 14px;
            }
            th, td {
                padding: 8px;
            }
        }
        @media (max-width: 480px) {
            .filters select, .filters input[type="text"] {
                width: 100%;
                margin-bottom: 10px;
            }
            table, th, td {
                font-size: 12px;
            }
            th, td {
                padding: 6px;
            }
            a.view-details, a.download-report {
                font-size: 12px;
                padding: 5px 8px;
                width: auto;
            }
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
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li class="has-submenu">
                <a href="#">Users</a>
                <ul class="submenu">
                    <li><a href="add_user.php">Add User</a></li>
                    <li><a href="manage_users.php">Manage Users</a></li>
                </ul>
            </li>
            <li><a href="students.php" class="active">Students</a></li>
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
        <div class="dashboard-container">
            <h1>All Students (Total: <?php echo $totalStudents; ?>)</h1>
            <div class="filters">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="campusFilter">
                    <option value="">All Campuses</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?php echo htmlspecialchars($campus); ?>"><?php echo htmlspecialchars($campus); ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" id="searchInput" placeholder="Search by ID, Name, or Email">
            </div>

            <div class="table-container">
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Grade</th>
                            <th>Campus</th>
                            <th>View / Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['gmail']); ?></td>
                                <td><?php echo htmlspecialchars($student['grade']); ?></td>
                                <td><?php echo htmlspecialchars($student['campus']); ?></td>
                                <td>
                                    <a href="student_details.php?id=<?php echo $student['id']; ?>" class="view-details">View Details</a>
                                    <a href="generate_pdf.php?id=<?php echo $student['id']; ?>" class="download-report" target="_blank">Download Report</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var submenuLinks = document.querySelectorAll('.has-submenu > a');
            
            submenuLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var parentLi = this.parentElement;
                    parentLi.classList.toggle('open');
                });
            });

            // Filter and search functionality
            var statusFilter = document.getElementById('statusFilter');
            var campusFilter = document.getElementById('campusFilter');
            var searchInput = document.getElementById('searchInput');
            var table = document.getElementById('studentsTable');
            var rows = table.getElementsByTagName('tr');

            function filterTable() {
                var statusValue = statusFilter.value.toLowerCase();
                var campusValue = campusFilter.value.toLowerCase();
                var searchValue = searchInput.value.toLowerCase();

                for (var i = 1; i < rows.length; i++) {
                    var row = rows[i];
                    var status = row.cells[3].innerText.toLowerCase(); // Assuming status is in the 4th column
                    var campus = row.cells[4].innerText.toLowerCase();
                    var rowData = row.innerText.toLowerCase();

                    if ((statusValue === '' || status.includes(statusValue)) &&
                        (campusValue === '' || campus.includes(campusValue)) &&
                        (searchValue === '' || rowData.includes(searchValue))) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }

            statusFilter.addEventListener('change', filterTable);
            campusFilter.addEventListener('change', filterTable);
            searchInput.addEventListener('keyup', filterTable);

            // Initial filter application (in case URL parameters are present)
            filterTable();
        });
    </script>
</body>
</html>