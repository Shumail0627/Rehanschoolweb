<?php
session_start();
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Check if principal is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Principal') {
    header('Location: login.php');
    exit();
}

$campus = $_SESSION['campus'];

// Fetch assignments for the principal's campus
$query = "SELECT a.*, u.name as student_name, u.roll_no 
          FROM assignments a 
          JOIN users u ON a.student_id = u.id 
          WHERE u.campus = '$campus' 
          ORDER BY a.due_date DESC";
$result = mysqli_query($conn, $query);

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $status = $_POST['status'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $update_query = "UPDATE assignments SET status = '$status', remarks = '$remarks' WHERE id = $assignment_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to refresh the page
    header('Location: principal_assignments.php');
    exit();
}

// Add these queries after the existing queries and before the HTML
$principalsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'Principal' AND campus = '$campus'";
$teachersQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'Teacher' AND campus = '$campus'";
$studentsQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'Student' AND campus = '$campus'";

$principalsResult = mysqli_query($conn, $principalsQuery);
$teachersResult = mysqli_query($conn, $teachersQuery);
$studentsResult = mysqli_query($conn, $studentsQuery);

$principalsData = mysqli_fetch_assoc($principalsResult)['count'];
$teachersData = mysqli_fetch_assoc($teachersResult)['count'];
$studentsData = mysqli_fetch_assoc($studentsResult)['count'];

$today = date('Y-m-d');

// Fetch all students and their task completion status
$students_query = "SELECT u.id, u.name, u.roll_no,
                   COUNT(sa.id) AS total_tasks,
                   SUM(CASE WHEN sa.link IS NOT NULL THEN 1 ELSE 0 END) AS completed_tasks
                   FROM users u
                   LEFT JOIN student_assignments sa ON u.id = sa.student_id AND sa.date = ?
                   WHERE u.campus = ? AND u.role = 'Student'
                   GROUP BY u.id
                   ORDER BY u.name";

$stmt = $conn->prepare($students_query);
$stmt->bind_param("ss", $today, $campus);
$stmt->execute();
$students_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard - Rehan School</title>
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
            color: white;
        }
        .user-info p {
            margin: 0;
            font-size: 14px;
            color: white;
        }
        .campus-info {
    font-size: 20px; /* Increase the font size */
    font-weight: bold; /* Make it bold */
    color: #007bff; /* You can adjust the color to match the design */
    margin-top: 10px; /* Adjust the margin as needed */
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
        .submenu {
            padding-left: 20px;
        }
        .submenu li a {
            font-size: 14px;
        }
        .content {
        margin-left: 270px;
        padding: 20px;
        width: calc(100% - 290px);
        box-sizing: border-box;
    }
    
    .dashboard-container {
        max-width: 100%;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
        h1 {
            font-size: 28px;
            color: #333;
            text-align: left;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #555;
            text-align: left;
            margin: 5px 0;
        }
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }

        .card {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1; /* Ensures cards take equal width */
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center; /* Centers content vertically */
            align-items: center; /* Centers content horizontally */
        }
        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .card p {
            font-size: 24px;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status-pending { color: orange; }
        .status-completed { color: green; }
        .status-late { color: red; }
        .task-status {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .task-status span {
            font-weight: bold;
        }
        .completed {
            color: green;
        }
        .incomplete {
            color: red;
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
    <li><a href="principal_dashboard.php" class="active">Dashboard</a></li>
    <li><a href="principal_students.php">Students</a></li>
    <li><a href="attendance.php">Attendance</a></li>
    <li><a href="principal_assignments.php">Assignments</a></li> <!-- New link -->
    <li><a href="logout.php">Logout</a></li>
</ul>
</div>
    
    <div class="content">
        <div class="dashboard-container">
            <h1>Principal Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['admin_name']; ?>!</p>
            <p class="campus-info">Islamabad Campus</p>

            <div class="summary-cards">
                <div class="card">
                    <h3>Total Principals</h3>
                    <p><?php echo $principalsData; ?></p>
                </div>
                <div class="card">
                    <h3>Total Teachers</h3>
                    <p><?php echo $teachersData; ?></p>
                </div>
                <div class="card">
                    <h3>Total Students</h3>
                    <p><?php echo $studentsData; ?></p>
                </div>
            </div>

            <div class="dashboard-container">
        <h2>Assignments Overview</h2>
        <p>Campus: <?php echo $campus; ?> | Date: <?php echo $today; ?></p>
        
        <table>
            <tr>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Task Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $students_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['roll_no'] ?? ''); ?></td>
                <td>
                    <div class="task-status">
                        <span class="completed">Completed: <?php echo $row['completed_tasks']; ?></span>
                        <span class="incomplete">Incomplete: <?php echo $row['total_tasks'] - $row['completed_tasks']; ?></span>
                    </div>
                </td>
                <td>
                    <a href="student_assignments.php?student_id=<?php echo $row['id']; ?>&date=<?php echo $today; ?>">View Details</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        </div>
    </div>

</body>
</html>
