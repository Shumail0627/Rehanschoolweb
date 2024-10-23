<?php
session_start();
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if teacher is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Teacher') {
    header('Location: login.php');
    exit();
}

$campus = $_SESSION['campus'];
$teacher_id = $_SESSION['admin_id'];

// Initialize variables
$studentsData = $classesData = $subjectsData = 0;

// Fetch total counts for the teacher's campus
$query = "SELECT 
    COUNT(CASE WHEN role = 'Student' THEN 1 END) as total_students,
    COUNT(DISTINCT class) as total_classes
FROM users 
WHERE campus = '$campus'";

$result = mysqli_query($conn, $query);
if ($result) {
    $data = mysqli_fetch_assoc($result);
    $studentsData = $data['total_students'];
    $classesData = $data['total_classes'];
} else {
    // Handle query error
    $error = "Error fetching data: " . mysqli_error($conn);
}

// For subjects, we'll just display the number of unique classes as a placeholder
$subjectsData = $classesData;

$today = date('Y-m-d');

// Fetch all students and their task completion status for the teacher's campus
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

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $status = $_POST['status'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $update_query = "UPDATE assignments SET status = '$status', remarks = '$remarks' WHERE id = $assignment_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to refresh the page
    header('Location: teacher_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Rehan School</title>
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
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin-top: 10px;
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
            background-color: #28a745;
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
            max-width: 1200px;
            width: 100%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px; /* Add some space between containers */
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
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .card p {
            font-size: 24px;
            margin: 0;
            color: white;
        }
        .assignments-overview {
            margin-top: 30px;
        }

        .assignments-overview h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .assignments-overview p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        .task-status {
            display: flex;
            justify-content: space-between;
        }

        .completed, .incomplete {
            font-weight: normal;
        }

        .action-link {
            color: #007bff;
            text-decoration: none;
        }

        .action-link:hover {
            text-decoration: underline;
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
        <p><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
        <p><?php echo htmlspecialchars($_SESSION['role']); ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="teacher_dashboard.php" class="active">Dashboard</a></li>
        <li><a href="teacher_classes.php">My Classes</a></li>
        <li><a href="teacher_subjects.php">My Subjects</a></li>
        <li><a href="teacher_attendance.php">Attendance</a></li>
        <li><a href="teacher_assignments.php">Assignments</a></li> <!-- New link -->
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
    
<div class="content">
    <div class="dashboard-container">
        <h1>Teacher Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
        <p class="campus-info"><?php echo htmlspecialchars($campus); ?> Campus</p>

        <div class="summary-cards">
            <div class="card">
                <h3>My Students</h3>
                <p><?php echo $studentsData; ?></p>
            </div>
            <div class="card">
                <h3>My Classes</h3>
                <p><?php echo $classesData; ?></p>
            </div>
            <div class="card">
                <h3>My Subjects</h3>
                <p><?php echo $subjectsData; ?></p>
            </div>
        </div>

        <div class="assignments-overview">
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
</div>
</body>
</html>