<?php
session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch the students for the dropdown list
$studentsQuery = "SELECT id, name, roll_no FROM users WHERE role = 'Student'";
$studentsResult = mysqli_query($conn, $studentsQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted form data
    $studentId = $_POST['student_id'];
    $attended = $_POST['attended'];

    // Insert the attendance record
    $attendanceQuery = "INSERT INTO attendance (student_id, attended) VALUES ('$studentId', '$attended')";
    if (mysqli_query($conn, $attendanceQuery)) {
        echo "<p>Attendance marked successfully!</p>";
    } else {
        echo "<p>Error marking attendance: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .attendance-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
            display: block;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Mark Attendance</h1>
    <div class="attendance-form">
        <form method="POST" action="mark_attendance.php">
            <label for="student_id">Select Student:</label>
            <select id="student_id" name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php while ($row = mysqli_fetch_assoc($studentsResult)) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['roll_no'] . ' - ' . $row['name']; ?></option>
                <?php } ?>
            </select>

            <label for="attended">Attendance:</label>
            <select id="attended" name="attended" required>
                <option value="1">Present</option>
                <option value="0">Absent</option>
            </select>

            <button type="submit">Mark Attendance</button>
        </form>
    </div>
</body>
</html>
