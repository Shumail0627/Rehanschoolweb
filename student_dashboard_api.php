<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'db.php';

if (isset($_GET['id'])) {
    $studentId = mysqli_real_escape_string($conn, $_GET['id']);
    
    $query = "SELECT u.*, u.picture_path,
          f.father_name, f.father_age, f.father_job, f.father_whatsapp,
          f.mother_name, f.mother_age, f.mother_job, f.mother_whatsapp,
          f.number_of_siblings,
          p.favorite_food_dishes, p.plan_for_crore_rupees, p.biggest_wish,
          p.vision_10_years, p.ideal_personalities,
          u.student_whatsapp
          FROM users u
          LEFT JOIN family_info f ON u.id = f.user_id
          LEFT JOIN personal_preferences p ON u.id = p.user_id
          WHERE u.id = '$studentId' AND u.role = 'Student'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        
        // Format dates
        $student['dob'] = $student['dob'] ? date('d-M-Y', strtotime($student['dob'])) : 'N/A';
        $student['doj'] = $student['doj'] ? date('d-M-Y', strtotime($student['doj'])) : 'N/A';
        
        // Fetch attendance data
        $attendanceQuery = "SELECT COUNT(*) AS total_days, SUM(attended) AS attended_days FROM attendance WHERE student_id = '$studentId'";
        $attendanceResult = mysqli_query($conn, $attendanceQuery);
        $attendance = mysqli_fetch_assoc($attendanceResult);
        
        $student['total_days'] = $attendance['total_days'] ?? 0;
        $student['attended_days'] = $attendance['attended_days'] ?? 0;
        
        // Ensure all fields exist, even if empty
        $fields = ['age', 'city', 'country', 'introduction', 'reason_for_joining', 'whatsapp_number'];
        foreach ($fields as $field) {
            if (!isset($student[$field])) {
                $student[$field] = '';
            }
        }
        
        echo json_encode($student);
    } else {
        echo json_encode(["error" => "Student not found"]);
    }
} else {
    echo json_encode(["error" => "No student ID provided"]);
}

mysqli_close($conn);
?>