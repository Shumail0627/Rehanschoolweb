<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($student)) {
    require 'db.php';
    $studentId = $_SESSION['student_id'];
    $query = "SELECT * FROM users WHERE id = '$studentId' AND role = 'Student'";
    $result = mysqli_query($conn, $query);
    $student = mysqli_fetch_assoc($result);
}
?>
<div class="sidebar">
    <h2># Rehan School</h2>
    <div class="user-info">
        <p><?php echo $student['name']; ?></p>
        <p><?php echo $student['role']; ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="student_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">My Profile</a></li>
        <li>
            <a href="#" class="submenu-toggle">My Academic Info</a>
            <ul class="submenu">
                <li><a href="my_attendance.php">My Attendance</a></li>
                <li><a href="my_fees.php">My Fees</a></li>
                <li><a href="assignments.php">Assignments</a></li>
                <li><a href="results.php">Results</a></li>
            </ul>
        </li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<style>
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
        color: white;
        font-weight: bold;
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
        color: rgba(255, 255, 255, 0.8);
    }
    .submenu {
        display: none;
        padding-left: 20px;
    }
    .submenu li a {
        font-size: 14px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
        });
    });
});
</script>
