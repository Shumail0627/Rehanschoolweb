<?php
session_start();
require 'db.php';

// Check if the user is logged in and is a Parent
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Parent') {
    header('Location: login.php');
    exit();
}

$parent_id = $_SESSION['admin_id'];

// Fetch parent's children with all details
$children_query = "SELECT * FROM users WHERE parent_id = ?";
$stmt = $conn->prepare($children_query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$children_result = $stmt->get_result();

// Function to get child's progress
function getChildProgress($conn, $child_id) {
    $progress_query = "SELECT 
        COUNT(*) as total_assignments,
        SUM(CASE WHEN link IS NOT NULL THEN 1 ELSE 0 END) as completed_assignments
    FROM student_assignments
    WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    
    $stmt = $conn->prepare($progress_query);
    $stmt->bind_param("i", $child_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $total = $result['total_assignments'];
    $completed = $result['completed_assignments'];
    
    return [
        'total' => $total,
        'completed' => $completed,
        'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Rehan School</title>
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
            color: #adb5bd;
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
        h1, h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .child-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .child-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .child-card h2 {
            color: #007bff;
            margin-top: 0;
        }
        .student-picture {
            text-align: center;
            margin-bottom: 20px;
        }
        .student-picture img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ffc300;
            padding: 5px;
        }
        .student-details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .student-details strong {
            font-weight: 600;
            color: #495057;
        }
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 4px;
            height: 20px;
            margin-top: 10px;
            overflow: hidden;
        }
        .progress {
            background-color: #007bff;
            height: 100%;
            transition: width 0.5s ease-in-out;
        }
        .btn {
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            padding: 10px 20px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .info-section {
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .info-section h3 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-item {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        .info-item:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .info-item strong {
            display: block;
            margin-bottom: 5px;
            color: #007bff;
        }
        .preferences {
            margin-top: 30px;
        }
        .preferences h3 {
            color: #333;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .preference-item {
            background-color: #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .preference-item strong {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2># Rehan School</h2>
        <div class="user-info">
            <p><?php echo $_SESSION['name']; ?></p>
            <p><?php echo $_SESSION['role']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="parent_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="parent_profile.php">My Profile</a></li>
            <li><a href="parent_children.php">My Children</a></li>
            <li><a href="parent_fees.php">Fees</a></li>
            <li><a href="parent_attendance.php">Attendance</a></li>
            <li><a href="parent_assignments.php">Assignments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="dashboard-container">
            <h1>Parent Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['name']; ?>!</p>
            
            <?php while ($child = $children_result->fetch_assoc()): ?>
    <?php $progress = getChildProgress($conn, $child['id']); ?>
    <div class="child-card">
        <div class="student-picture">
            <img src="<?php echo !empty($child['picture_path']) ? $child['picture_path'] : './images/default_profile.png'; ?>" alt="<?php echo $child['name']; ?>'s picture">
        </div>
        <h2><?php echo $child['name']; ?></h2>
        
        <!-- Student Information -->
        <div class="info-section">
            <h3>Student Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Name:</strong>
                    <p><?php echo $child['name']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Age:</strong>
                    <p><?php echo $child['child_age']; ?> years</p>
                </div>
                <div class="info-item">
                    <strong>Date of Birth:</strong>
                    <p><?php echo $child['dob']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Campus:</strong>
                    <p><?php echo $child['campus']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Roll Number:</strong>
                    <p><?php echo $child['roll_no']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Date of Joining:</strong>
                    <p><?php echo $child['doa']; ?></p>
                </div>
                <div class="info-item">
                    <strong>City:</strong>
                    <p><?php echo $child['city']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Country:</strong>
                    <p><?php echo $child['country']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Grade:</strong>
                    <p><?php echo $child['grade']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Email:</strong>
                    <p><?php echo $child['gmail']; ?></p>
                </div>
            </div>
            
            <div class="info-item" style="margin-top: 20px;">
                <strong>Reason for Joining:</strong>
                <p><?php echo $child['reason_for_joining']; ?></p>
            </div>
            
            <div class="info-item" style="margin-top: 20px;">
                <strong>Introduction:</strong>
                <p><?php echo $child['introduction']; ?></p>
            </div>
        </div>

        <!-- Family Information -->
        <div class="info-section">
            <h3>Family Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Father's Name:</strong>
                    <p><?php echo $child['father_name']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Father's Age:</strong>
                    <p><?php echo $child['father_age']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Father's Job:</strong>
                    <p><?php echo $child['father_job']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Father's WhatsApp:</strong>
                    <p><?php echo $child['father_whatsapp']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Mother's Name:</strong>
                    <p><?php echo $child['mother_name']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Mother's Age:</strong>
                    <p><?php echo $child['mother_age']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Mother's Job:</strong>
                    <p><?php echo $child['mother_job']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Mother's WhatsApp:</strong>
                    <p><?php echo $child['mother_whatsapp']; ?></p>
                </div>
                <div class="info-item">
                    <strong>Number of Siblings:</strong>
                    <p><?php echo $child['number_of_siblings']; ?></p>
                </div>
            </div>
        </div>

        <!-- Preferences -->
        <div class="info-section">
            <h3>Preferences</h3>
            <div class="preference-item">
                <strong>3 Favorite Food Dishes:</strong>
                <p><?php echo $child['favorite_food_dishes']; ?></p>
            </div>
            <div class="preference-item">
                <strong>Plan for 1 Crore Rupees:</strong>
                <p><?php echo $child['plan_for_crore_rupees']; ?></p>
            </div>
            <div class="preference-item">
                <strong>Biggest Wish:</strong>
                <p><?php echo $child['biggest_wish']; ?></p>
            </div>
            <div class="preference-item">
                <strong>10 Years Vision:</strong>
                <p><?php echo $child['vision_10_years']; ?></p>
            </div>
            <div class="preference-item">
                <strong>3 Ideal Personalities:</strong>
                <p><?php echo $child['ideal_personalities']; ?></p>
            </div>
            <div class="preference-item">
                <strong>WhatsApp Number:</strong>
                <p><?php echo $child['student_whatsapp']; ?></p>
            </div>
        </div>

        <!-- Assignments Progress -->
        <h3>Assignments Progress</h3>
        <p>Completed: <?php echo $progress['completed']; ?> / <?php echo $progress['total']; ?></p>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo $progress['percentage']; ?>%;"></div>
        </div>
        <p>Progress: <?php echo $progress['percentage']; ?>%</p>
        <a href="parent_child_details.php?child_id=<?php echo $child['id']; ?>" class="btn">View Detailed Progress</a>
        </div>
    <?php endwhile; ?>
        </div>
    </div>
</body>
</html>