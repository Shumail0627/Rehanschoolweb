<?php
session_start();
require 'db.php'; // Database connection

// Check if the user is logged in and is a Principal
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Principal') {
    header('Location: login.php');
    exit();
}

// Fetch the campus associated with the logged-in Principal
$campus = $_SESSION['campus'];

// Fetch students who belong to the same campus as the logged-in Principal
$query = "SELECT * FROM users WHERE role = 'Student' AND campus = '$campus'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Students - Rehan School</title>
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
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background-color: #007bff;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }
        .content h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .btn-view {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
        }
        .btn-view:hover {
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
            <p><?php echo $_SESSION['campus']; ?> Campus</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="principal_dashboard.php">Dashboard</a></li>
            <li><a href="principal_students.php" class="active">Students</a></li>
            <li><a href="attendance.php">Attendance</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Students of <?php echo $campus; ?> Campus</h1>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Class</th>
                <th>Roll Number</th>
                <th>Actions</th>
            </tr>
            <?php while ($student = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['gmail']; ?></td>
                    <td><?php echo $student['grade']; ?></td>
                    <td><?php echo $student['roll_no']; ?></td>
                    <td><a href="student_details.php?id=<?php echo $student['id']; ?>" class="btn-view">View Details</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
