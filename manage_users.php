<?php
session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit();
}

// Fetch all users
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

$users = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
} else {
    $users = null;
}

// Delete user
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $deleteQuery = "DELETE FROM users WHERE id = '$userId'";
    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Rehan School</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .action-buttons a {
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .edit-button {
            background-color: #28a745;
        }
        .delete-button {
            background-color: #dc3545;
        }
        .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .back-button:hover {
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
            <h1>Manage Users</h1>
            
            <?php if ($users): ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Campus</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['gmail']; ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td><?php echo $user['campus']; ?></td>
                            <td class="action-buttons">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="edit-button">Edit</a>
                                <a href="manage_users.php?delete=<?php echo $user['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>

            <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
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
