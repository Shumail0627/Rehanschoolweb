<?php
session_start();
require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];
    $payment_date = $_POST['payment_date'];

    // Insert fee record into the database
    $query = "INSERT INTO fees (student_id, month, year, amount, status, payment_date, created_at) 
              VALUES ('$student_id', '$month', '$year', '$amount', '$status', '$payment_date', NOW())";

    if (mysqli_query($conn, $query)) {
        // Get the ID of the last inserted record
        $fee_id = mysqli_insert_id($conn);

        // Redirect to the generate voucher page
        header("Location: generate_voucher_pdf.php?fee_id=$fee_id");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch students to display in the dropdown
$students_query = "SELECT id, name, roll_no FROM users WHERE role = 'Student'";
$students_result = mysqli_query($conn, $students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student Fee - Rehan School</title>
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
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input[type="text"], input[type="number"], input[type="date"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .form-group-inline {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-group-inline .form-group {
            flex: 1;
        }
        .form-group-inline input[type="number"] {
            flex: 0 0 48%;
        }
        button {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            font-size: 18px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Add Student Fee</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Select Student</option>
                    <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo $student['name'] . " (" . $student['roll_no'] . ")"; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label for="month">Month</label>
                    <select name="month" id="month" required>
                        <option value="">Select Month</option>
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="number" name="year" id="year" required placeholder="Year">
                </div>
            </div>
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" required placeholder="Amount">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="">Select Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                </select>
            </div>
            <div class="form-group">
                <label for="payment_date">Payment Date</label>
                <input type="date" name="payment_date" id="payment_date" required>
            </div>
            <button type="submit">Add Fee</button>
        </form>
    </div>
</body>
</html>
