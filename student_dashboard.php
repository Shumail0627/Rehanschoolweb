<?php
session_start();
require 'db.php'; // Database connection

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch student details
$studentId = $_SESSION['student_id'];
$query = "SELECT * FROM users WHERE id = '$studentId' AND role = 'Student'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

// Fetch attendance summary
$attendanceQuery = "SELECT COUNT(*) AS total_days, SUM(attended) AS attended_days FROM attendance WHERE student_id = '$studentId'";
$attendanceResult = mysqli_query($conn, $attendanceQuery);
$attendance = mysqli_fetch_assoc($attendanceResult);

$totalDays = $attendance['total_days'];
$attendedDays = $attendance['attended_days'];
$absentDays = $totalDays - $attendedDays;

// Fetch fee status
$feeQuery = "SELECT SUM(amount) AS total_paid FROM fees WHERE student_id = '$studentId'";
$feeResult = mysqli_query($conn, $feeQuery);
$fee = mysqli_fetch_assoc($feeResult);
$totalPaid = $fee['total_paid'] ?? 0;
$monthlyFee = $student['monthly_fee'] ?? 0;
$outstandingFee = $monthlyFee - $totalPaid;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        /* CSS Styling similar to your existing dashboards */
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
        }
        .card {
             background-color: #007bff;
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
             transition: all 0.3s ease;
         }
         .card:hover {
             transform: translateY(-5px);
             box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
         }
         .card h3 {
             font-size: 18px;
             margin-bottom: 10px;
         }
         .card p {
             font-size: 24px;
             margin: 0;
         }
         .card-link {
             text-decoration: none;
             color: inherit;
             flex: 1;
         }
 
        canvas {
            width: 100% !important;
            max-width: 400px;
            margin: 0 auto;
        }
        .info-section {
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .info-section h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            transform: translateY(-3px);
        }

        .info-item strong {
            display: block;
            color: #007bff;
            margin-bottom: 5px;
        }

        .info-item p {
            margin: 0;
            color: #555;
        }

        .preferences {
            margin-top: 30px;
        }

        .preferences h2 {
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
        .attendance-overview {
        margin-top: 30px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .chart-container {
        height: 300px;
        max-width: 500px;
        margin: 0 auto;
    }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2># Rehan School</h2>
        <div class="user-info">
            <p><?php echo $student['name']; ?></p>
            <p><?php echo $student['role']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="my_attendance.php">My Attendance</a></li>
            <li><a href="my_fees.php">My Fees</a></li>
            <li><a href="assignments.php">Assignments</a></li>
            <li><a href="results.php">Results</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="content">
        <div class="dashboard-container">
            <h1>Welcome, <?php echo $student['name']; ?> - Student Dashboard</h1>
            
            <!-- Summary Cards -->
            <div class="summary-cards">
            <a href="my_attendance.php" class="card-link">
                <div class="card">
                    <h3>Attendance</h3>
                    <p><?php echo $attendedDays . ' / ' . $totalDays; ?></p>
                </div>
                </a>
                <div class="card">
                    <h3>Outstanding Fees</h3>
                    <p><?php echo 'PKR ' . $outstandingFee; ?></p>
                </div>
                <div class="card">
                    <h3>Monthly Fee</h3>
                    <p><?php echo 'PKR ' . $monthlyFee; ?></p>
                </div>
            </div>
            
            
            <!-- Student Information -->
            <div class="info-section">
                <h2>Student Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Name:</strong>
                        <p><?php echo $student['name']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Age:</strong>
                        <p><?php echo $student['age']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Date of Birth:</strong>
                        <p><?php echo $student['dob']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Campus:</strong>
                        <p><?php echo $student['campus']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Roll Number:</strong>
                        <p><?php echo $student['roll_no']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Date of Joining:</strong>
                        <p><?php echo $student['doj']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>City, Country:</strong>
                        <p><?php echo $student['city'] . ', ' . $student['country']; ?></p>
                    </div>
                </div>
                
                <div class="info-item" style="margin-top: 20px;">
                    <strong>Reason for Joining:</strong>
                    <p><?php echo $student['reason_for_joining']; ?></p>
                </div>
                
                <div class="info-item" style="margin-top: 20px;">
                    <strong>Introduction:</strong>
                    <p><?php echo $student['introduction']; ?></p>
                </div>

                <h2>Family Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Father's Name:</strong>
                        <p><?php echo $student['father_name']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Father's Age:</strong>
                        <p><?php echo $student['father_age']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Father's Job:</strong>
                        <p><?php echo $student['father_job']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Father's WhatsApp:</strong>
                        <p><?php echo $student['father_whatsapp']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Mother's Name:</strong>
                        <p><?php echo $student['mother_name']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Mother's Age:</strong>
                        <p><?php echo $student['mother_age']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Mother's Job:</strong>
                        <p><?php echo $student['mother_job']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Mother's WhatsApp:</strong>
                        <p><?php echo $student['mother_whatsapp']; ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Number of Siblings:</strong>
                        <p><?php echo $student['number_of_siblings']; ?></p>
                    </div>
                </div>

                <div class="preferences">
                    <h2>Preferences</h2>
                    <div class="preference-item">
                        <strong>3 Favorite Food Dishes:</strong>
                        <p><?php echo $student['favorite_food_dishes']; ?></p>
                    </div>
                    <div class="preference-item">
                        <strong>Plan for 1 Crore Rupees:</strong>
                        <p><?php echo $student['plan_for_crore_rupees']; ?></p>
                    </div>
                    <div class="preference-item">
                        <strong>Biggest Wish:</strong>
                        <p><?php echo $student['biggest_wish']; ?></p>
                    </div>
                    <div class="preference-item">
                        <strong>10 Years Vision:</strong>
                        <p><?php echo $student['vision_10_years']; ?></p>
                    </div>
                    <div class="preference-item">
                        <strong>3 Ideal Personalities:</strong>
                        <p><?php echo $student['ideal_personalities']; ?></p>
                    </div>
                    <div class="preference-item">
                        <strong>WhatsApp Number:</strong>
                        <p><?php echo $student['student_whatsapp']; ?></p>
                    </div>
                </div>
            </div>
      <!-- Attendance Overview Section -->
      <div class="attendance-overview">
            <h2>Attendance Overview</h2>
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Attended Days', 'Absent Days'],
            datasets: [{
                data: [<?php echo $attendedDays; ?>, <?php echo $absentDays; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderColor: ['#28a745', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            }
        }
    });
    </script>
</body>
</html>
