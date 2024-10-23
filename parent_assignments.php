<?php
session_start();
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if parent is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Parent') {
    header('Location: login.php');
    exit();
}

$parent_id = $_SESSION['admin_id'];

// Fetch parent's children
$children_query = "SELECT id, name, roll_no FROM users WHERE parent_id = ? AND role = 'Student' ORDER BY name";
$stmt = $conn->prepare($children_query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$children_result = $stmt->get_result();

// If a child is selected, fetch their assignments
$selected_child = isset($_GET['child_id']) ? $_GET['child_id'] : null;

if ($selected_child) {
    $assignments_query = "SELECT sa.*, u.name as student_name, u.roll_no 
                          FROM student_assignments sa
                          JOIN users u ON sa.student_id = u.id 
                          WHERE u.id = ? 
                          ORDER BY sa.date DESC";
    $stmt = $conn->prepare($assignments_query);
    $stmt->bind_param("i", $selected_child);
    $stmt->execute();
    $assignments_result = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Children's Assignments - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        .children-list {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .child-card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .child-card.selected {
            background-color: #007bff;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .view-link {
            color: #007bff;
            text-decoration: none;
        }
        .view-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Children's Assignments</h1>

        <div class="children-list">
            <?php while ($child = $children_result->fetch_assoc()) : ?>
                <div class="child-card <?php echo ($selected_child == $child['id']) ? 'selected' : ''; ?>"
                     onclick="window.location.href='parent_assignments.php?child_id=<?php echo $child['id']; ?>'">
                    <h3><?php echo $child['name']; ?></h3>
                    <p>Roll No: <?php echo $child['roll_no']; ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($selected_child) : ?>
            <?php 
            $child_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE id = $selected_child"))['name'];
            ?>
            <h2>Assignments for <?php echo $child_name; ?></h2>
            <?php if ($assignments_result->num_rows > 0) : ?>
                <table>
                    <tr>
                        <th>Assignment</th>
                        <th>Date</th>
                        <th>Submission</th>
                        <th>Remarks</th>
                    </tr>
                    <?php while ($row = $assignments_result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['assignment_name']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['date'])); ?></td>
                            <td>
                                <?php if ($row['link']) : ?>
                                    <a href="<?php echo $row['link']; ?>" class="view-link" target="_blank">View Submission</a>
                                <?php else : ?>
                                    Not submitted
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['remarks'] ? $row['remarks'] : 'No remarks yet'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else : ?>
                <p>No assignments found for this child.</p>
            <?php endif; ?>
        <?php elseif ($children_result->num_rows > 0) : ?>
            <p>Please select a child to view their assignments.</p>
        <?php else : ?>
            <p>No children found for this parent.</p>
        <?php endif; ?>
    </div>
</body>
</html>