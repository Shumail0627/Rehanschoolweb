<?php
session_start();
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student details
$stmt = $conn->prepare("SELECT name, gmail, roll_no, campus, role FROM users WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Define the list of daily assignments
$assignments = [
    'Interview in English',
    'Interview in Urdu',
    'TEDx Review in Urdu',
    'TEDx Review in English',
    'Faceless Video',
    '3 Social Media Posts',
    'Interview with Call Annie',
    'Yoga',
    'Meditation',
    'Osoji'
];

// Get the date for which to show assignments
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch completed assignments for the student on the selected date
$completed_assignments = [];
$stmt = $conn->prepare("SELECT assignment_name, link, remarks FROM student_assignments WHERE student_id = ? AND date = ?");
$stmt->bind_param("is", $student_id, $date);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $completed_assignments[$row['assignment_name']] = [
        'link' => $row['link'],
        'remarks' => $row['remarks']
    ];
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignment = $_POST['assignment'];
    $link = $_POST['link'];
    $submission_date = date('Y-m-d'); // Current date when the assignment is submitted

    // Check if the user is trying to update a past assignment
    if ($date < date('Y-m-d') && $student['role'] != 'Teacher') {
        $error_message = "Only teachers can update past assignments.";
    } else {
        // Check for duplicate link
        $stmt = $conn->prepare("SELECT id FROM student_assignments WHERE link = ? AND student_id != ? AND date = ?");
        $stmt->bind_param("sis", $link, $student_id, $date);
        $stmt->execute();
        $duplicate_result = $stmt->get_result();
        if ($duplicate_result->num_rows > 0) {
            $error_message = "This link has already been submitted by another student.";
        } else {
            // Insert or update the assignment
            $stmt = $conn->prepare("INSERT INTO student_assignments (student_id, assignment_name, link, date, submission_date) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE link = ?, submission_date = ?");
            $stmt->bind_param("issssss", $student_id, $assignment, $link, $date, $submission_date, $link, $submission_date);
            if ($stmt->execute()) {
                $completed_assignments[$assignment] = [
                    'link' => $link,
                    'remarks' => ''
                ];
                $success_message = "Assignment submitted successfully!";
            } else {
                $error_message = "Error submitting assignment. Please try again.";
            }
        }
        $stmt->close();
    }
}

// Check if all assignments are completed
$all_completed = count($completed_assignments) == count($assignments);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Assignments - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3498db;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }
        .student-info {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1, h2 {
            margin: 0;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: white;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        li:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .completed {
            border-left: 5px solid #2ecc71;
        }
        .remaining {
            border-left: 5px solid #e74c3c;
        }
        form {
            display: flex;
            align-items: center;
        }
        input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="submit"] {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .error {
            color: #e74c3c;
            background-color: #fadbd8;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success {
            color: #2ecc71;
            background-color: #d4efdf;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .assignment-name {
            font-weight: 500;
            margin-bottom: 10px;
        }
        .completed-text {
            color: #2ecc71;
            font-weight: 500;
        }
        .view-submission {
            color: #3498db;
            text-decoration: none;
            margin-left: 10px;
        }
        .view-submission:hover {
            text-decoration: underline;
        }
        .date-selector {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .date-selector form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-selector label {
            font-weight: bold;
            margin-right: 10px;
        }

        .date-selector input[type="date"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .date-selector input[type="submit"] {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .date-selector input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .motivation {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .remarks {
            margin-top: 10px;
            font-size: 14px;
        }

        .checking {
            color: #f39c12;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <!-- Your sidebar code here -->
    </div>
    
    <div class="container">
        <div class="header">
            <h1>Daily Assignments</h1>
        </div>
        
        <div class="student-info">
            <h2><?php echo htmlspecialchars($student['name']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($student['gmail']); ?></p>
            <p>Roll No: <?php echo htmlspecialchars($student['roll_no']); ?></p>
            <p>Campus: <?php echo htmlspecialchars($student['campus']); ?></p>
        </div>
        
        <div class="date-selector">
    <form method="get">
        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" value="<?php echo $date; ?>" max="<?php echo date('Y-m-d'); ?>">
        <input type="submit" value="View Assignments">
    </form>
</div>
        
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <?php if ($all_completed && $date == date('Y-m-d')): ?>
            <div class="motivation">
                Great progress! You've completed all tasks for today. Keep up the good work!
            </div>
        <?php endif; ?>

        <ul>
            <?php foreach ($assignments as $assignment): ?>
                <li class="<?php echo isset($completed_assignments[$assignment]) ? 'completed' : 'remaining'; ?>">
                    <div class="assignment-name"><?php echo htmlspecialchars($assignment); ?></div>
                    <?php if (isset($completed_assignments[$assignment])): ?>
                        <span class="completed-text">Completed</span>
                        <a href="<?php echo htmlspecialchars($completed_assignments[$assignment]['link']); ?>" class="view-submission" target="_blank">View Submission</a>
                        <div class="remarks">
                            <?php if (!empty($completed_assignments[$assignment]['remarks'])): ?>
                                <strong>Remarks:</strong> <?php echo htmlspecialchars($completed_assignments[$assignment]['remarks']); ?>
                            <?php else: ?>
                                <span class="checking">Submitted</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if ($date == date('Y-m-d') || $student['role'] == 'Teacher'): ?>
                            <form method="post" action="">
                                <input type="hidden" name="assignment" value="<?php echo htmlspecialchars($assignment); ?>">
                                <input type="text" name="link" placeholder="Enter submission link" required>
                                <input type="submit" value="Submit">
                            </form>
                        <?php else: ?>
                            <span>Not completed</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    var dateInput = document.getElementById('date');
    dateInput.addEventListener('change', function() {
        var selectedDate = new Date(this.value);
        var formattedDate = selectedDate.getDate() + ' ' + 
                            selectedDate.toLocaleString('default', { month: 'short' }) + ' ' + 
                            selectedDate.getFullYear();
        this.nextElementSibling.value = 'View Assignments for ' + formattedDate;
    });

    // Format the initial date
    var initialDate = new Date(dateInput.value);
    var formattedInitialDate = initialDate.getDate() + ' ' + 
                               initialDate.toLocaleString('default', { month: 'short' }) + ' ' + 
                               initialDate.getFullYear();
    dateInput.nextElementSibling.value = 'View Assignments for ' + formattedInitialDate;
});
</script>

</body>
</html>

