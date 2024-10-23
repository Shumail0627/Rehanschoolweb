<?php
session_start();
require 'db.php'; // Include the database connection

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Get student ID from session
$studentId = $_SESSION['student_id'];

// Fetch student fee details
$feeQuery = "SELECT payment_date, amount FROM fees WHERE student_id = '$studentId' ORDER BY payment_date ASC";
$feeResult = mysqli_query($conn, $feeQuery);

$feeRecords = [];
if (mysqli_num_rows($feeResult) > 0) {
    while ($row = mysqli_fetch_assoc($feeResult)) {
        $feeRecords[] = $row;
    }
} else {
    $feeRecords = null;
}

// Calculate totals
$totalPaid = array_sum(array_column($feeRecords, 'amount'));
$monthlyFee = 5000; // Assuming a fixed monthly fee
$outstandingFee = $monthlyFee * count($feeRecords) - $totalPaid;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fee Details</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .details-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 900px;
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
    <div class="details-container">
        <h1>My Fee Details</h1>
        <p><strong>Total Paid:</strong> PKR <?php echo $totalPaid; ?></p>
        <p><strong>Outstanding Fee:</strong> PKR <?php echo $outstandingFee; ?></p>
        <p><strong>Monthly Fee:</strong> PKR <?php echo $monthlyFee; ?></p>
        
        <table>
            <tr>
                <th>Date</th>
                <th>Amount Paid</th>
            </tr>
            <?php if ($feeRecords): ?>
                <?php foreach ($feeRecords as $record): ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($record['payment_date'])); ?></td>
                        <td>PKR <?php echo $record['amount']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No fee records found.</td>
                </tr>
            <?php endif; ?>
        </table>

        <a href="student_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
