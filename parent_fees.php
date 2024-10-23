<?php
session_start();
require 'db.php';

// Check if the user is logged in and is a Parent
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Parent') {
    header('Location: login.php');
    exit();
}

$parent_id = $_SESSION['admin_id'];

// Fetch parent's children and their fees details
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.roll_no, 
        u.campus, 
        u.monthly_fee, 
        IFNULL(SUM(f.amount), 0) as total_fees_paid,
        (u.monthly_fee * PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(u.doa, '%Y%m'))) - IFNULL(SUM(f.amount), 0) as balance
    FROM 
        users u 
    LEFT JOIN 
        fees f ON u.id = f.student_id 
    WHERE 
        u.parent_id = ? AND u.role = 'Student'
    GROUP BY 
        u.id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Children's Fees - Rehan School</title>
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
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .details-btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .details-btn:hover {
            background-color: #0056b3;
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
        <h1>My Children's Fees</h1>
        <table>
            <tr>
                <th>Child Name</th>
                <th>Roll Number</th>
                <th>Campus</th>
                <th>Monthly Fee</th>
                <th>Total Fees Paid</th>
                <th>Balance</th>
                <th>Details</th>
            </tr>
            <?php while ($child = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $child['name']; ?></td>
                <td><?php echo $child['roll_no']; ?></td>
                <td><?php echo $child['campus']; ?></td>
                <td>Rs. <?php echo number_format($child['monthly_fee'], 2); ?></td>
                <td>Rs. <?php echo number_format($child['total_fees_paid'], 2); ?></td>
                <td class="<?php echo $child['balance'] > 0 ? 'balance-negative' : 'balance-positive'; ?>">
                    Rs. <?php echo number_format(abs($child['balance']), 2); ?>
                    <?php echo $child['balance'] > 0 ? '(Due)' : '(Advance)'; ?>
                </td>
                <td><a class="details-btn" href="child_fee_details.php?id=<?php echo $child['id']; ?>">View Details</a></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>