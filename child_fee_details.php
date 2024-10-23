<?php
session_start();
require 'db.php';

// Check if the user is logged in and is a Parent
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Parent') {
    header('Location: login.php');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$parent_id = $_SESSION['admin_id'];
$child_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify that the child belongs to this parent
$verify_query = "SELECT * FROM users WHERE id = ? AND parent_id = ? AND role = 'Student'";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("ii", $child_id, $parent_id);
$verify_stmt->execute();
$child_result = $verify_stmt->get_result();

if ($child_result->num_rows == 0) {
    die("Unauthorized access or invalid child ID.");
}

$child = $child_result->fetch_assoc();

// Fetch child's fee details
$fee_query = "
    SELECT 
        f.id,
        f.amount,
        f.payment_date,
        f.month,
        f.year,
        f.payment_method,
        f.status
    FROM 
        fees f
    WHERE 
        f.student_id = ?
    ORDER BY 
        f.payment_date DESC, f.year DESC, f.month DESC
";

$fee_stmt = $conn->prepare($fee_query);
$fee_stmt->bind_param("i", $child_id);
$fee_stmt->execute();
$fee_result = $fee_stmt->get_result();

// Calculate total fees paid and balance
$total_paid = 0;
$fees = [];
while ($fee = $fee_result->fetch_assoc()) {
    if ($fee['status'] == 'paid') {
        $total_paid += $fee['amount'];
    }
    $fees[] = $fee;
}

$current_date = new DateTime();
$admission_date = new DateTime($child['doa']);
$months_enrolled = $admission_date->diff($current_date)->y * 12 + $admission_date->diff($current_date)->m;

$total_due = $child['monthly_fee'] * $months_enrolled;
$balance = $total_due - $total_paid;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $child['name']; ?>'s Fee Details - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .child-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .child-info p {
            margin: 5px 0;
        }
        .fee-summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .fee-summary div {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            flex: 1;
            margin: 0 10px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .balance-positive {
            color: green;
        }
        .balance-negative {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $child['name']; ?>'s Fee Details</h1>
        
        <div class="child-info">
            <p><strong>Name:</strong> <?php echo $child['name']; ?></p>
            <p><strong>Roll Number:</strong> <?php echo $child['roll_no']; ?></p>
            <p><strong>Campus:</strong> <?php echo $child['campus']; ?></p>
            <p><strong>Date of Admission:</strong> <?php echo $child['doa']; ?></p>
            <p><strong>Monthly Fee:</strong> Rs. <?php echo number_format($child['monthly_fee'], 2); ?></p>
        </div>

        <div class="fee-summary">
            <div>
                <h3>Total Due</h3>
                <p>Rs. <?php echo number_format($total_due, 2); ?></p>
            </div>
            <div>
                <h3>Total Paid</h3>
                <p>Rs. <?php echo number_format($total_paid, 2); ?></p>
            </div>
            <div>
                <h3>Balance</h3>
                <p class="<?php echo $balance > 0 ? 'balance-negative' : 'balance-positive'; ?>">
                    Rs. <?php echo number_format(abs($balance), 2); ?>
                    <?php echo $balance > 0 ? '(Due)' : '(Advance)'; ?>
                </p>
            </div>
        </div>

        <h2>Fee Payment History</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Month/Year</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
            </tr>
            <?php foreach ($fees as $fee): ?>
            <tr>
                <td><?php echo $fee['payment_date'] ? date('d-m-Y', strtotime($fee['payment_date'])) : 'N/A'; ?></td>
                <td><?php echo $fee['month'] . ' ' . $fee['year']; ?></td>
                <td>Rs. <?php echo number_format($fee['amount'], 2); ?></td>
                <td><?php echo $fee['payment_method'] ? $fee['payment_method'] : 'N/A'; ?></td>
                <td><?php echo ucfirst($fee['status']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
