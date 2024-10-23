<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'db.php';

if (isset($_GET['id'])) {
    $studentId = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Fetch fee records
    $query = "SELECT payment_date, amount FROM fees WHERE student_id = '$studentId' ORDER BY payment_date DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $feeRecords = [];
        $totalPaid = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $feeRecords[] = $row;
            $totalPaid += $row['amount'];
        }
        
        // Assuming monthly fee is 5000
        $monthlyFee = 5000;
        $outstandingFee = $monthlyFee * count($feeRecords) - $totalPaid;
        
        $response = [
            "total_paid" => $totalPaid,
            "outstanding_fee" => $outstandingFee,
            "monthly_fee" => $monthlyFee,
            "fee_records" => $feeRecords
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(["error" => "Failed to fetch fee records"]);
    }
} else {
    echo json_encode(["error" => "No student ID provided"]);
}
?>
