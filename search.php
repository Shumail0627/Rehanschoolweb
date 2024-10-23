<?php
session_start();
require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get the campus associated with the admin
$campus = isset($_SESSION['campus']) ? $_SESSION['campus'] : '';

// Capture the search query
$query = isset($_GET['query']) ? $_GET['query'] : '';
$searchResults = [];

if (!empty($query)) {
    $query = mysqli_real_escape_string($conn, $query);

    // SQL query to search by name, email, roll number, and campus
    $sql = "SELECT * FROM users WHERE role = 'Student' AND (
                name LIKE '%$query%' OR 
                gmail LIKE '%$query%' OR 
                roll_no LIKE '%$query%' OR 
                campus LIKE '%$query%'
            )";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Filter the results by campus if campus is set in session
            if (empty($campus) || $row['campus'] == $campus) {
                $searchResults[] = $row;
            }
        }
    }
} else {
    // Fetch all students if no search query is provided
    $sql = "SELECT * FROM users WHERE role = 'Student'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (empty($campus) || $row['campus'] == $campus) {
                $searchResults[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Rehan School</title>
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
            width: 100%;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 28px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .search-results {
            margin-top: 20px;
        }
        .result-item {
            background-color: #f9f9f9;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .result-item h3 {
            font-size: 20px;
            color: #007bff;
            margin: 0;
        }
        .result-item p {
            margin: 5px 0;
            color: #555;
        }
        .result-item a {
            padding: 8px 12px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .result-item a:hover {
            background-color: #0056b3;
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
            <li><a href="students.php">Students</a></li>
            <li class="has-submenu">
                <a href="#">Student Fees</a>
                <ul class="submenu">
                    <li><a href="add_student_fee.php">Add Student Fee</a></li>
                    <li><a href="show_student_fees.php">Show Student Fees</a></li>
                    <li><a href="generate_voucher_pdf.php">Generate Voucher PDF</a></li>
                </ul>
            </li>
            <li><a href="attendance.php">Attendance</a></li>
            <li><a href="online_payments.php">Online Payments</a></li>
            <li><a href="staff_info.php">Staff Information</a></li>
            <li><a href="admission_freeze.php">Admission Freeze</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="container">
            <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>

            <?php if (empty($searchResults)): ?>
                <p>No results found.</p>
            <?php else: ?>
                <div class="search-results">
                    <?php foreach ($searchResults as $student): ?>
                        <div class="result-item">
                            <div>
                                <h3><?php echo $student['name']; ?></h3>
                                <p>Email: <?php echo $student['gmail']; ?></p>
                                <p>Roll Number: <?php echo $student['roll_no']; ?></p>
                                <p>Campus: <?php echo $student['campus']; ?></p>
                            </div>
                            <a href="student_details.php?id=<?php echo $student['id']; ?>">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
