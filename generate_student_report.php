<?php
require('fpdf/fpdf.php');
require('db.php');

// Check if the campus is provided
if (isset($_POST['campus'])) {
    $campus = $_POST['campus'];
} else {
    die('No campus provided');
}

// Fetch all students for the selected campus
$query = "SELECT * FROM users WHERE campus = ? AND role = 'Student'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $campus);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('No students found in this campus');
}

// FPDF Class Extension
class PDF extends FPDF
{
    // Page Header
    function Header()
    {
        $this->SetFillColor(62, 142, 245);
        $this->Rect(0, 0, 210, 40, 'F');
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(255);
        $this->Cell(0, 20, 'Campus Students Report', 0, 1, 'C');
        $this->Ln(5);
    }

    // Page Footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Section Title
    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 16);
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(62, 142, 245);
        $this->Cell(0, 10, $title, 0, 1, 'L', true);
        $this->Ln(5);
    }

    // Display a label-value row for student info
    function InfoRow($label, $value)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0);
        $this->Cell(60, 8, $label . ':', 0);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 8, $value, 0);
        $this->Ln(2);  // Add spacing between rows
    }
}

// Initialize PDF
$pdf = new PDF();
$pdf->AddPage();

// Section Title for the Campus
$pdf->SectionTitle('Campus: ' . $campus);

// Current Month for Fee Calculation
$currentMonth = date('F');
$currentYear = date('Y');

// Loop through all students and add their details to the PDF
while ($student = $result->fetch_assoc()) {
    $pdf->SectionTitle('Student Information - ' . $student['name']);

    // Fetch total fees paid by the student
    $feesQuery = "SELECT SUM(amount) as total_paid FROM fees WHERE student_id = ? AND year = ?";
    $feesStmt = $conn->prepare($feesQuery);
    $feesStmt->bind_param("ii", $student['id'], $currentYear);
    $feesStmt->execute();
    $feesResult = $feesStmt->get_result();
    $feesData = $feesResult->fetch_assoc();
    $totalPaid = $feesData['total_paid'] ?? 0;

    // Calculate how much fees should have been paid until the current month
    $currentMonthIndex = date('n'); // Get the current month number (1 for Jan, 2 for Feb, etc.)
    $totalDue = $student['monthly_fees'] * $currentMonthIndex;

    // Calculate remaining fees
    $remainingFees = $totalDue - $totalPaid;

    // Info to display in the PDF
    $info = [
        'Student ID' => $student['id'],
        'Name' => $student['name'],
        'Email' => $student['gmail'],
        'Roll Number' => $student['roll_no'],
        'Class' => $student['class'],
        'Campus' => $student['campus'],
        'Date of Joining' => $student['doj'],
        'Total Fees Paid' => 'PKR ' . number_format($totalPaid, 0),
        'Remaining Fees' => 'PKR ' . ($remainingFees > 0 ? number_format($remainingFees, 0) : '0'),
    ];

    foreach ($info as $label => $value) {
        if (!empty($value)) {
            $pdf->InfoRow($label, $value);
        }
    }

    $pdf->Ln(10);  // Add some space before the next student
}

// Output the PDF for download
$pdf->Output('D', 'Campus_Student_Report_' . $campus . '.pdf');

?>
