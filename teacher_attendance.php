<?php
session_start();
require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Determine the role of the logged-in user
$role = $_SESSION['role'];
$campus = $_SESSION['campus'];

// Modify the query based on the role
if ($role == 'Admin') {
    // Admin can see students from all campuses
    $query = "SELECT id, name, roll_no, campus FROM users WHERE role = 'Student'";
} else {
    // Principals can only see students from their own campus
    $query = "SELECT id, name, roll_no, campus FROM users WHERE role = 'Student' AND campus = '$campus'";
}

$result = mysqli_query($conn, $query);

// Handle form submission to mark attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    foreach ($_POST['attendance'] as $student_id => $status) {
        $status = $status == 'Present' ? 1 : 0;
        $sql = "INSERT INTO attendance (student_id, date, attended) VALUES ('$student_id', '$date', '$status') 
                ON DUPLICATE KEY UPDATE attended = '$status'";
        mysqli_query($conn, $sql);
    }
    $success_message = "Attendance marked successfully for $date.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Rehan School</title>
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
            z-index: 100; /* Ensures sidebar is on top */
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
        .attendance-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        h1 {
            font-size: 28px;
            color: #333;
            text-align: left;
            margin-bottom: 20px;
        }
        .date-picker {
            margin-bottom: 20px;
        }
        .student-list {
            width: 100%;
            border-collapse: collapse;
        }
        .student-list th, .student-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .student-list th {
            background-color: #007bff;
            color: white;
        }
        .submit-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .success-message {
            color: green;
            margin-bottom: 20px;
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
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

    <div class="content">
        <div class="attendance-container">
            <h1>Mark Attendance</h1>

            <?php if (isset($success_message)) : ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="date-picker">
                    <label for="date">Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <table class="student-list">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Roll Number</th>
                            <th>Campus</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['roll_no']; ?></td>
                                <td><?php echo $student['campus']; ?></td>
                                <td>
                                    <select name="attendance[<?php echo $student['id']; ?>]">
                                        <option value="Present">Present</option>
                                        <option value="Absent">Absent</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <button type="submit" class="submit-btn">Submit Attendance</button>
            </form>
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
