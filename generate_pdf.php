<?php
require('fpdf/fpdf.php');
require('db.php');

if (!isset($_GET['id'])) {
    die('No student ID provided');
}

$student_id = $_GET['id'];

// Fetch student details
$query = "SELECT * FROM users WHERE id = ? AND role = 'Student'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die('Student not found');
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFillColor(62, 142, 245);
        $this->Rect(0, 0, 210, 40, 'F');
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(255);
        $this->Cell(0, 20, 'Student Information', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 16);
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(62, 142, 245);
        $this->Cell(0, 10, $title, 0, 1, 'L', true);
        $this->Ln(5);
    }

    function InfoRow($label, $value)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0);
        $this->Cell(60, 8, $label . ':', 0);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 8, $value, 0);
    }
}

$pdf = new PDF();
$pdf->AddPage();

$pdf->SectionTitle('Personal Information');

$info = [
    'Student ID' => $student['id'],
    'Student Name' => $student['name'],
    'Email' => $student['gmail'],
    'Age' => $student['age'],
    'Date of Birth' => $student['dob'],
    'Class' => $student['class'],
    'Campus' => $student['campus'],
    'Roll Number' => $student['roll_no'],
    'Date of Joining' => $student['doj'],
    'Reason for Joining' => $student['reason_for_joining'],
    'City' => $student['city'],
    'Country' => $student['country'],
    'Favorite Food Dishes' => $student['favorite_food_dishes'],
    'Ideal Personalities' => $student['ideal_personalities']
];

foreach ($info as $label => $value) {
    $pdf->InfoRow($label, $value);
}

$pdf->Output('D', 'Student_Report_' . $student['roll_no'] . '.pdf');