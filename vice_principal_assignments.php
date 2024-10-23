<?php
session_start();
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if vice principal is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Vice-Principal') {
    header('Location: login.php');
    exit();
}

$campus = $_SESSION['campus'];

// Fetch students for the vice principal's campus
$students_query = "SELECT id, name, roll_no FROM users WHERE role = 'Student' AND campus = '$campus' ORDER BY name";
$students_result = mysqli_query($conn, $students_query);

if (!$students_result) {
    die("Query failed: " . mysqli_error($conn));
}

// If a student is selected, fetch their assignments
$selected_student = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if ($selected_student) {
    $assignments_query = "SELECT sa.*, u.name as student_name, u.roll_no 
                          FROM student_assignments sa
                          JOIN users u ON sa.student_id = u.id 
                          WHERE u.id = $selected_student 
                          ORDER BY sa.date DESC";
    $assignments_result = mysqli_query($conn, $assignments_query);

    if (!$assignments_result) {
        die("Query failed: " . mysqli_error($conn));
    }
}

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $update_query = "UPDATE student_assignments SET remarks = '$remarks' WHERE id = $assignment_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to refresh the page
    header("Location: vice_principal_assignments.php?student_id=$selected_student");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments Overview - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        /* Styles remain the same as in principal_assignments.php */
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
            color: #007bff;
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
            background-color: #007bff;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 290px);
        }
        .dashboard-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        .student-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .student-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .student-card.selected {
            background-color: #007bff;
            color: white;
            border-color: #0056b3;
        }
        .student-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .student-roll {
            font-size: 0.9em;
            color: #666;
        }
        .student-card.selected .student-roll {
            color: #e0e0e0;
        }
        .section-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
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
            <li><a href="vice_principal_dashboard.php">Dashboard</a></li>
            <li><a href="vice_principal_students.php">Students</a></li>
            <li><a href="attendance.php">Attendance</a></li>
            <li><a href="vice_principal_assignments.php" class="active">Assignments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="dashboard-container">
            <h1 class="section-title">Assignments Overview</h1>
            <p>Campus: <?php echo $campus; ?></p>

            <h2 class="section-title">Students</h2>
            <div class="student-list">
                <?php while ($student = mysqli_fetch_assoc($students_result)) : ?>
                    <div class="student-card" 
                        onclick="window.location.href='vice_principal_assignments.php?student_id=<?php echo $student['id']; ?>'">
                        <div class="student-name"><?php echo $student['name']; ?></div>
                        <div class="student-roll"><?php echo $student['roll_no']; ?></div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($selected_student) : ?>
                <h2>Assignments for <?php echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE id = $selected_student"))['name']; ?></h2>
                <table>
                    <tr>
                        <th>Assignment</th>
                        <th>Date</th>
                        <th>Link</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_assoc($assignments_result)) : ?>
                        <tr>
                            <td><?php echo $row['assignment_name']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><a href="<?php echo $row['link']; ?>" target="_blank">View Submission</a></td>
                            <td><?php echo $row['remarks']; ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="assignment_id" value="<?php echo $row['id']; ?>">
                                    <input type="text" name="remarks" value="<?php echo $row['remarks']; ?>" placeholder="Remarks">
                                    <button type="submit" name="update_assignment">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php elseif ($students_result->num_rows > 0) : ?>
                <p>Please select a student to view their assignments.</p>
            <?php else : ?>
                <p>No students found for this campus.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>