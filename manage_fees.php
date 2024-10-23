<?php
session_start();
require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch fee records
$query = "SELECT fees.id as fee_id, users.name as student_name, fees.month, fees.year, fees.amount, fees.status 
          FROM fees 
          JOIN users ON fees.student_id = users.id 
          ORDER BY fees.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fees - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .table-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        table td {
            font-size: 16px;
        }
        .actions a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            margin-right: 10px;
        }
        .actions a:hover {
            color: #0056b3;
        }
        .btn-add {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <h1>Manage Fees</h1>
        <a href="add_student_fee.php" class="btn-add">Add New Fee</a>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Month</th>
                    <th>Year</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['fee_id']; ?></td>
                            <td><?php echo $row['student_name']; ?></td>
                            <td><?php echo $row['month']; ?></td>
                            <td><?php echo $row['year']; ?></td>
                            <td><?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                            <td class="actions">
                                <a href="generate_voucher_pdf.php?fee_id=<?php echo $row['fee_id']; ?>">Generate Voucher</a>
                                <a href="edit_fee.php?fee_id=<?php echo $row['fee_id']; ?>">Edit</a>
                                <a href="delete_fee.php?fee_id=<?php echo $row['fee_id']; ?>" onclick="return confirm('Are you sure you want to delete this fee?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No fee records found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
