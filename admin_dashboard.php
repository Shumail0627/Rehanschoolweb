<?php
session_start();
require 'db.php'; // Database connection

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch total counts for each role
$roleData = [];
$roles = ['Admin', 'Principal', 'Teacher', 'Student'];
foreach ($roles as $role) {
    $query = "SELECT COUNT(id) as total FROM users WHERE role = '$role'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $roleData[$role] = $data['total'];
}

// Fetch total counts for each campus and fees data
$campusData = [];
$campuses = ['Korangi', 'Munawwar', 'Islamabad', 'Online'];

foreach ($campuses as $campus) {
    // Fetch total students for each campus
    $query = "SELECT COUNT(id) as total_students FROM users WHERE campus = '$campus' AND role = 'Student'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $campusData[$campus]['students'] = $data['total_students'];
    
    // Fetch total teachers for each campus
    $query = "SELECT COUNT(id) as total_teachers FROM users WHERE campus = '$campus' AND role = 'Teacher'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $campusData[$campus]['teachers'] = $data['total_teachers'];
    
    // Fetch total fees received for each campus
    $query = "SELECT SUM(amount) as total_fees FROM fees WHERE student_id IN (SELECT id FROM users WHERE campus = '$campus')";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    $campusData[$campus]['total_fees'] = $data['total_fees'] ?? 0; // Handle null values
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>

    * {
    transition: all 0.3s ease-in-out;
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* General styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f6f9;
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

/* Main Content */
.content {
    margin-left: 270px;
    padding: 20px;
    width: 100%;
}

.dashboard-container {
    max-width: 1200px;
    width: 100%;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
    flex-wrap: wrap; /* Ensures wrapping on smaller screens */
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
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.card:hover {
    transform: scale(1.05); /* Slight scaling on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Deeper shadow on hover */
}

.card h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.card p {
    font-size: 24px;
    margin: 0;
}

.campus-cards {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap; /* Ensures wrapping on smaller screens */
    margin-top: 20px;
}

.campus-card {
    background-color: #FFF9C4; /* Campus card color */
    margin-top: 20px;
    color: #333333; /* Dark text color */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for depth */
    flex: 1 1 calc(25% - 20px); /* Responsive width with margin */
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative; /* Ensure content is positioned relative to the card */
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.campus-card:hover {
    transform: scale(1.05); /* Slight scaling on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Deeper shadow on hover */
}

.campus-card h4 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 15px;
}

.campus-card p {
    font-size: 18px;
    margin: 10px 0;
}

.campus-card a {
    margin-top: 15px;
    display: inline-block;
    padding: 10px 20px;
    background-color: #FFC300; /* Golden-yellow button */
    color: #333333; /* Dark text color */
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease-in-out;
}

.campus-card a:hover {
    background-color: #FFB000; /* Darker blue on hover */
}

/* New styles for Total Fees Section */
    .total-fees-section {
        margin-top: 40px;
    }

    .total-fees-cards {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap; /* Ensures wrapping on smaller screens */
    }

    .total-fees-card {
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
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .total-fees-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }

    .total-fees-card h4 {
        font-size: 18px;
        margin-bottom: 10px;
    }

    .total-fees-card p {
        font-size: 24px;
        margin: 0;
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
    <div class="search-bar">
    <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Search..." required>
    </form>
</div>
    <ul class="sidebar-menu">
        <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
        <li class="has-submenu">
    <a href="#">Users</a>
    <ul class="submenu">
        <li><a href="add_user.php">Add User</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
    </ul>
</li>

<!-- Assignments Section (Newly Added) -->
<li class="has-submenu">
            <a href="#">Assignments</a>
            <ul class="submenu">
                <li><a href="assignments.php">View Assignments</a></li> <!-- Link to Assignment Page -->
            </ul>
        </li>
        
        <li><a href="students.php">Students</a></li>
        <li class="has-submenu">
            <a href="#">Student Fees</a>
            <ul class="submenu">
                <li><a href="add_student_fee.php">Add Student Fee</a></li>
                <li><a href="show_student_fees.php">Show Student Fees</a></li>
                <li><a href="manage_fees.php">Generate Voucher PDF</a></li>
            </ul>
        </li>
        <li><a href="attendance.php">Attendance</a></li>
        <li><a href="online_payments.php">Online Payments</a></li>
        <li><a href="staff_info.php">Staff Information</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
    <div class="content">
        <div class="dashboard-container">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['admin_name']; ?>!</p>

            <div class="summary-cards">
                <div class="card" onclick="window.location.href='admins.php';" style="cursor: pointer;">
                    <h3>Total Admins</h3>
                    <p><?php echo $roleData['Admin']; ?></p>
                </div>
                <div class="card" onclick="window.location.href='principals.php';" style="cursor: pointer;">
                    <h3>Total Principals</h3>
                    <p><?php echo $roleData['Principal']; ?></p>
                </div>
                <div class="card" onclick="window.location.href='teachers.php';" style="cursor: pointer;">
                    <h3>Total Teachers</h3>
                    <p><?php echo $roleData['Teacher']; ?></p>
                </div>
                <div class="card" onclick="window.location.href='students.php';" style="cursor: pointer;">
    <h3>Total Students</h3>
    <p><?php echo $roleData['Student']; ?></p>
</div>
            </div>

            <div class="campus-cards">
                <?php foreach ($campusData as $campus => $data): ?>
                    <div class="campus-card">
                        <h4><?php echo $campus; ?> Campus</h4>
                        <p>Total Students: <?php echo $data['students']; ?></p>
                        <p>Total Teachers: <?php echo $data['teachers']; ?></p>
                        <a href="campus_details.php?campus=<?php echo urlencode($campus); ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- New section for Total Fees Received below campus cards -->
            <div class="total-fees-section">
                <h2>Total Fees Received</h2>
                <div class="total-fees-cards">
                    <?php foreach ($campusData as $campus => $data): ?>
                        <div class="total-fees-card">
                            <h4><?php echo $campus; ?> Campus</h4>
                            <p>PKR <?php echo number_format($data['total_fees'], 0); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

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
