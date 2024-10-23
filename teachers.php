<?php
session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all teachers from the database
$query = "SELECT id, name, gmail, campus, status FROM users WHERE role = 'Teacher'";
$result = mysqli_query($conn, $query);
$teachers = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $teachers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers - Rehan School</title>
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
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: #007bff;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }
        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            font-size: 28px;
            color: #333;
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
        table tr:hover {
            background-color: #f1f1f1;
        }
        .details-button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .details-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2># Rehan School</h2>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="students.php">Students</a></li>
            <li><a href="teachers.php" class="active">Teachers</a></li>
            <li><a href="student_fees.php">Student Fees</a></li>
            <li><a href="attendance.php">Attendance</a></li>
            <li><a href="online_payments.php">Online Payments</a></li>
            <li><a href="staff_info.php">Staff Information</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="table-container">
            <h1>Teachers List</h1>
            <?php if (!empty($teachers)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Campus</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo $teacher['id']; ?></td>
                                <td><?php echo $teacher['name']; ?></td>
                                <td><?php echo $teacher['gmail']; ?></td>
                                <td><?php echo $teacher['campus']; ?></td>
                                <td><?php echo $teacher['status']; ?></td>
                                <td><a href="teacher_details.php?id=<?php echo $teacher['id']; ?>" class="details-button">View Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No teachers found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
