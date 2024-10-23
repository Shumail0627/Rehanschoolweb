<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; // Include the database connection

$response = array();

// Function to sanitize input
function sanitize_input($input) {
    return htmlspecialchars(strip_tags($input));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Define required fields
    $required_fields = ['name', 'email', 'password', 'role'];
    $missing_fields = [];

    // Check for missing required fields
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        $response['status'] = 'error';
        $response['message'] = 'Required fields are missing: ' . implode(', ', $missing_fields);
        echo json_encode($response);
        exit();
    }

    // Sanitize and fetch input data
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = sanitize_input($_POST['role']);
    $campus = sanitize_input($_POST['campus'] ?? '');
    $roll_no = sanitize_input($_POST['roll_no'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'Active');
    $dob = sanitize_input($_POST['dob'] ?? '');
    $doj = sanitize_input($_POST['doj'] ?? '');
    $age = sanitize_input($_POST['age'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $country = sanitize_input($_POST['country'] ?? '');
    $father_name = sanitize_input($_POST['father_name'] ?? '');
    $father_age = sanitize_input($_POST['father_age'] ?? '');
    $father_job = sanitize_input($_POST['father_job'] ?? '');
    $father_whatsapp = sanitize_input($_POST['father_whatsapp'] ?? '');
    $mother_name = sanitize_input($_POST['mother_name'] ?? '');
    $mother_age = sanitize_input($_POST['mother_age'] ?? '');
    $mother_job = sanitize_input($_POST['mother_job'] ?? '');
    $mother_whatsapp = sanitize_input($_POST['mother_whatsapp'] ?? '');
    $number_of_siblings = sanitize_input($_POST['number_of_siblings'] ?? '');
    $student_whatsapp = sanitize_input($_POST['student_whatsapp'] ?? '');
    $class = sanitize_input($_POST['class'] ?? '');
    $reason_for_joining = sanitize_input($_POST['reason_for_joining'] ?? '');
    $favorite_food_dishes = sanitize_input($_POST['favorite_food_dishes'] ?? '');
    $ideal_personalities = sanitize_input($_POST['ideal_personalities'] ?? '');
    $plan_for_crore_rupees = sanitize_input($_POST['plan_for_crore_rupees'] ?? '');
    $biggest_wish = sanitize_input($_POST['biggest_wish'] ?? '');
    $vision_10_years = sanitize_input($_POST['vision_10_years'] ?? '');

    // Handle picture upload
    $picturePath = 'uploads/default.jpg'; // Default to a placeholder image
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileExtension = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $newFileName = "user_" . time() . "." . $fileExtension;
        $targetFile = $targetDir . $newFileName;

        $allowedExtensions = array("jpg", "jpeg", "png", "gif");
        if (in_array($fileExtension, $allowedExtensions)) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
                $picturePath = $targetFile;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error uploading file.';
                echo json_encode($response);
                exit();
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.';
            echo json_encode($response);
            exit();
        }
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE gmail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email already exists.';
    } else {
        // Insert the new user into the users table
        $sql = "INSERT INTO users (name, gmail, password, role, campus, roll_no, status, dob, doj, city, country, age, picture_path, 
                father_name, father_age, father_job, father_whatsapp, mother_name, mother_age, mother_job, mother_whatsapp, 
                number_of_siblings, student_whatsapp, class, reason_for_joining, favorite_food_dishes, ideal_personalities, 
                plan_for_crore_rupees, biggest_wish, vision_10_years) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssssssssssssssss", 
            $name, $email, $password, $role, $campus, $roll_no, $status, $dob, $doj, $city, $country, $age, $picturePath,
            $father_name, $father_age, $father_job, $father_whatsapp, $mother_name, $mother_age, $mother_job, $mother_whatsapp,
            $number_of_siblings, $student_whatsapp, $class, $reason_for_joining, $favorite_food_dishes, $ideal_personalities,
            $plan_for_crore_rupees, $biggest_wish, $vision_10_years);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = "New $role has been added successfully!";
            $response['user_id'] = $stmt->insert_id;
        } else {
            $response['status'] = 'error';
            $response['message'] = "Error: " . $stmt->error;
        }
    }
    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
}

echo json_encode($response);
$conn->close();
?>