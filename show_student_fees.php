<?php
session_start();
require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch students and their total fees paid
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.roll_no, 
        u.campus, 
        u.monthly_fee, 
        IFNULL(SUM(f.amount), 0) as total_fees_paid 
    FROM 
        users u 
    LEFT JOIN 
        fees f ON u.id = f.student_id 
    WHERE 
        u.role = 'Student' 
    GROUP BY 
        u.id
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fees - Rehan School</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
            background-color: #f2f2f2;
        }

        .details-btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
        }

        .details-btn:hover {
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
        <input type="text" placeholder="Search...">
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
                <li><a href="generate_voucher_pdf.php">Generate Voucher PDF</a></li>
            </ul>
        </li>
        <li><a href="attendance.php">Attendance</a></li>
        <li><a href="online_payments.php">Online Payments</a></li>
        <li><a href="staff_info.php">Staff Information</a></li>
        <li><a href="admission_freeze.php">Admission Freeze</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
    <div class="content">
        <div class="dashboard-container">
            <h1>Student Fees</h1>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Roll Number</th>
                    <th>Campus</th>
                    <th>Monthly Fee</th>
                    <th>Total Fees Paid</th>
                    <th>Details</th>
                </tr>
                <?php while ($student = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['roll_no']; ?></td>
                    <td><?php echo $student['campus']; ?></td>
                    <td><?php echo $student['monthly_fee']; ?></td>
                    <td><?php echo $student['total_fees_paid']; ?></td>
                    <td><a class="details-btn" href="student_details.php?id=<?php echo $student['id']; ?>">View Details</a></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

<script>
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
