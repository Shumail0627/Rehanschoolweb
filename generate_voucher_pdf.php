<?php
session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Check if fee ID is provided
if (!isset($_GET['fee_id'])) {
    die("No fee ID provided.");
}

$fee_id = $_GET['fee_id'];

// Fetch fee details
$query = "SELECT fees.*, users.name as student_name, users.roll_no as student_roll_no, users.campus
          FROM fees 
          JOIN users ON fees.student_id = users.id 
          WHERE fees.id = '$fee_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("No fee record found.");
}

$fee = mysqli_fetch_assoc($result);

// Generate PDF using FPDF
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // School Logo
        $this->Image('images/logo.png', 10, 6, 30); // Update the path here to your logo
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, 'Fee Voucher', 0, 1, 'C');
        $this->Ln(10);
        // Date
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Date: ' . date('d M Y'), 0, 1, 'R');
        $this->Ln(5);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Instantiation of inherited class
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Table with student details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Record Copy', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);

// Roll No instead of Student ID
$pdf->Cell(40, 10, 'Roll No:', 1);
$pdf->Cell(0, 10, $fee['student_roll_no'], 1, 1);

$pdf->Cell(40, 10, 'Student Name:', 1);
$pdf->Cell(0, 10, $fee['student_name'], 1, 1);

$pdf->Cell(40, 10, 'Campus:', 1);
$pdf->Cell(0, 10, $fee['campus'], 1, 1);

$pdf->Cell(40, 10, 'Year:', 1);
$pdf->Cell(0, 10, $fee['year'], 1, 1);

$pdf->Cell(40, 10, 'Month:', 1);
$pdf->Cell(0, 10, $fee['month'], 1, 1);

$pdf->Cell(40, 10, 'Amount:', 1);
$pdf->Cell(0, 10, 'PKR ' . number_format($fee['amount'], 2), 1, 1);

$pdf->Cell(40, 10, 'Status:', 1);
$pdf->Cell(0, 10, ucfirst($fee['status']), 1, 1);

$pdf->Cell(40, 10, 'Generated At:', 1);
$pdf->Cell(0, 10, $fee['created_at'], 1, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Thank you for your payment. For any queries, contact the finance office.', 0, 1, 'C');

$pdf->Ln(5);
$noteText = "Note: Fee dues must be paid before the 10th of every month. Once paid, the fee will not be refunded. A reissue fee of Rs. 50 will be charged for issuing a new voucher.";
$pdf->MultiCell(0, 10, $noteText, 0, 'C');

// Output the PDF
$pdf->Output('I', 'Fee_Voucher_' . $fee['student_roll_no'] . '.pdf');

