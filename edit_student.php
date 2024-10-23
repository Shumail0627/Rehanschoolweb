<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get student ID from the query parameter
$studentId = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($studentId)) {
    // If no student ID is provided, redirect to the dashboard
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch the student's details
$studentQuery = "SELECT * FROM users WHERE id = '$studentId' AND role = 'Student'";
$studentResult = mysqli_query($conn, $studentQuery);

if (mysqli_num_rows($studentResult) == 1) {
    $student = mysqli_fetch_assoc($studentResult);
} else {
    // If no student is found, redirect to the dashboard
    header('Location: admin_dashboard.php');
    exit();
}

// Handle form submission for editing student details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $doj = mysqli_real_escape_string($conn, $_POST['doj']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $campus = mysqli_real_escape_string($conn, $_POST['campus']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $reason_for_joining = mysqli_real_escape_string($conn, $_POST['reason_for_joining']);
    $favorite_food_dishes = mysqli_real_escape_string($conn, $_POST['favorite_food_dishes']);
    $ideal_personalities = mysqli_real_escape_string($conn, $_POST['ideal_personalities']);

    // New fields
    $father_age = mysqli_real_escape_string($conn, $_POST['father_age']);
    $father_job = mysqli_real_escape_string($conn, $_POST['father_job']);
    $father_whatsapp = mysqli_real_escape_string($conn, $_POST['father_whatsapp']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $mother_age = mysqli_real_escape_string($conn, $_POST['mother_age']);
    $mother_job = mysqli_real_escape_string($conn, $_POST['mother_job']);
    $mother_whatsapp = mysqli_real_escape_string($conn, $_POST['mother_whatsapp']);
    $number_of_siblings = mysqli_real_escape_string($conn, $_POST['number_of_siblings']);
    $plan_for_crore_rupees = mysqli_real_escape_string($conn, $_POST['plan_for_crore_rupees']);
    $biggest_wish = mysqli_real_escape_string($conn, $_POST['biggest_wish']);
    $vision_10_years = mysqli_real_escape_string($conn, $_POST['vision_10_years']);
    $student_whatsapp = mysqli_real_escape_string($conn, $_POST['student_whatsapp']);

    // Handle picture upload
    $picturePath = $student['picture_path']; // Default to current picture path
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                die('Failed to create folders...');
            }
        }
        $fileExtension = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $newFileName = "student_" . $studentId . "_" . time() . "." . $fileExtension;
        $targetFile = $targetDir . $newFileName;
        
        $allowedExtensions = array("jpg", "jpeg", "png", "gif");
        if (in_array($fileExtension, $allowedExtensions)) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
                $picturePath = $targetFile; // Update picture path if upload is successful
            } else {
                die("Error uploading file. Error code: " . $_FILES["picture"]["error"]);
            }
        } else {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }
    }

    // Update student details
    $updateQuery = "UPDATE users SET 
        name = '$name', 
        age = '$age', 
        dob = '$dob', 
        doj = '$doj', 
        picture_path = '$picturePath', 
        father_name = '$father_name', 
        class = '$class', 
        campus = '$campus', 
        city = '$city', 
        country = '$country', 
        reason_for_joining = '$reason_for_joining', 
        favorite_food_dishes = '$favorite_food_dishes', 
        ideal_personalities = '$ideal_personalities',
        father_age = '$father_age',
        father_job = '$father_job',
        father_whatsapp = '$father_whatsapp',
        mother_name = '$mother_name',
        mother_age = '$mother_age',
        mother_job = '$mother_job',
        mother_whatsapp = '$mother_whatsapp',
        number_of_siblings = '$number_of_siblings',
        plan_for_crore_rupees = '$plan_for_crore_rupees',
        biggest_wish = '$biggest_wish',
        vision_10_years = '$vision_10_years',
        student_whatsapp = '$student_whatsapp'
        WHERE id = '$studentId'";

    if (mysqli_query($conn, $updateQuery)) {
        header("Location: student_details.php?id=$studentId");
        exit();
    } else {
        die("Error updating record: " . mysqli_error($conn));
    }
}

// Format the dates for display
$formattedDob = !empty($student['dob']) ? date('Y-m-d', strtotime($student['dob'])) : '';
$formattedDoj = !empty($student['doj']) ? date('Y-m-d', strtotime($student['doj'])) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
   .profile-section {
    width: 30%;
    padding: 20px;
    background-color: #f8f9fa;
    border-right: 1px solid #e0e0e0;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center; /* Centers content horizontally */
}

.profile-picture {
    width: 100%; /* Ensures the container takes full width */
    display: flex;
    justify-content: center; /* Centers the content horizontally */
    flex-direction: column;
    align-items: center;
}

.profile-picture img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 20px;
    border: 5px solid #ffc300;
    padding: 5px;
}
        .upload-button {
            background-color: #ff5733;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .upload-instructions {
            font-size: 12px;
            color: #555;
            margin-bottom: 10px;
        }
        .edit-profile-section {
            width: 70%;
            padding: 40px;
        }
        .edit-profile-section h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .edit-profile-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .form-group {
            width: 48%;
            margin-bottom: 15px;
            
        }
        .form-group.full-width {
            width: 100%;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        .update-button {
            background-color: #ff5733;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-picture">
                <img src="<?php echo $student['picture_path'] ? $student['picture_path'] : 'path/to/default/image.jpg'; ?>" alt="Profile Picture" id="profileImg">
                <input type="file" id="uploadPic" name="picture" accept="image/*" style="display:none;" />
                <button type="button" class="upload-button" onclick="document.getElementById('uploadPic').click();">Upload New Photo</button>
                <p class="upload-instructions">Upload a Picture. Larger image will be resized automatically. Maximum upload size is 1 MB.</p>
                <?php if(isset($uploadError)): ?>
                    <p class="error"><?php echo $uploadError; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Edit Profile Section -->
        <div class="edit-profile-section">
            <h2>Edit Student Profile</h2>
            <?php if(isset($updateError)): ?>
                <p class="error"><?php echo $updateError; ?></p>
            <?php endif; ?>
            <form class="edit-profile-form" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="age">Age</label>
        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($student['age']); ?>" required>
    </div>
    <div class="form-group">
        <label for="dob">Date of Birth (D.O.B)</label>
        <input type="date" id="dob" name="dob" value="<?php echo $formattedDob; ?>" required>
    </div>
    <div class="form-group">
        <label for="doj">Date of Joining (D.O.J)</label>
        <input type="date" id="doj" name="doj" value="<?php echo $formattedDoj; ?>" required>
    </div>
    <div class="form-group">
        <label for="fatherName">Father's Name</label>
        <input type="text" id="fatherName" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="fatherAge">Father's Age</label>
        <input type="number" id="fatherAge" name="father_age" value="<?php echo htmlspecialchars($student['father_age']); ?>">
    </div>
    <div class="form-group">
        <label for="fatherJob">Father's Job</label>
        <input type="text" id="fatherJob" name="father_job" value="<?php echo htmlspecialchars($student['father_job']); ?>">
    </div>
    <div class="form-group">
        <label for="fatherWhatsapp">Father's WhatsApp</label>
        <input type="text" id="fatherWhatsapp" name="father_whatsapp" value="<?php echo htmlspecialchars($student['father_whatsapp']); ?>">
    </div>
    <div class="form-group">
        <label for="motherName">Mother's Name</label>
        <input type="text" id="motherName" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>">
    </div>
    <div class="form-group">
        <label for="motherAge">Mother's Age</label>
        <input type="number" id="motherAge" name="mother_age" value="<?php echo htmlspecialchars($student['mother_age']); ?>">
    </div>
    <div class="form-group">
        <label for="motherJob">Mother's Job</label>
        <input type="text" id="motherJob" name="mother_job" value="<?php echo htmlspecialchars($student['mother_job']); ?>">
    </div>
    <div class="form-group">
        <label for="motherWhatsapp">Mother's WhatsApp</label>
        <input type="text" id="motherWhatsapp" name="mother_whatsapp" value="<?php echo htmlspecialchars($student['mother_whatsapp']); ?>">
    </div>
    <div class="form-group">
        <label for="siblings">Number of Siblings</label>
        <input type="number" id="siblings" name="number_of_siblings" value="<?php echo htmlspecialchars($student['number_of_siblings']); ?>">
    </div>
    <div class="form-group">
        <label for="class">Class</label>
        <input type="text" id="class" name="class" value="<?php echo htmlspecialchars($student['class']); ?>" required>
    </div>
    <div class="form-group">
        <label for="campus">Campus</label>
        <input type="text" id="campus" name="campus" value="<?php echo htmlspecialchars($student['campus']); ?>" required>
    </div>
    <div class="form-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($student['city']); ?>" required>
    </div>
    <div class="form-group">
        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($student['country']); ?>" required>
    </div>
    <div class="form-group full-width">
        <label for="reason">Reason for Joining</label>
        <textarea id="reason" name="reason_for_joining" required><?php echo htmlspecialchars($student['reason_for_joining']); ?></textarea>
    </div>
    <div class="form-group full-width">
        <label for="food">Favorite Food Dishes</label>
        <input type="text" id="food" name="favorite_food_dishes" value="<?php echo htmlspecialchars($student['favorite_food_dishes']); ?>" required>
    </div>
    <div class="form-group full-width">
        <label for="personalities">Ideal Personalities</label>
        <input type="text" id="personalities" name="ideal_personalities" value="<?php echo htmlspecialchars($student['ideal_personalities']); ?>" required>
    </div>
    <div class="form-group full-width">
        <label for="planCrore">Plans if given 1 crore rupees</label>
        <textarea id="planCrore" name="plan_for_crore_rupees"><?php echo htmlspecialchars($student['plan_for_crore_rupees']); ?></textarea>
    </div>
    <div class="form-group full-width">
        <label for="biggestWish">Biggest Wish</label>
        <textarea id="biggestWish" name="biggest_wish"><?php echo htmlspecialchars($student['biggest_wish']); ?></textarea>
    </div>
    <div class="form-group full-width">
        <label for="vision">Vision for 10 Years Ahead</label>
        <textarea id="vision" name="vision_10_years"><?php echo htmlspecialchars($student['vision_10_years']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="studentWhatsapp">Student's WhatsApp</label>
        <input type="text" id="studentWhatsapp" name="student_whatsapp" value="<?php echo htmlspecialchars($student['student_whatsapp']); ?>">
    </div>
    <input type="file" name="picture" id="pictureInput" style="display: none;">
    <button type="submit" class="update-button">Update info</button>
</form>
        </div>
    </div>

    <script>
        document.getElementById('uploadPic').addEventListener('change', function() {
            const [file] = this.files;
            if (file) {
                document.getElementById('profileImg').src = URL.createObjectURL(file);
                document.getElementById('pictureInput').files = this.files;
            }
        });
    </script>
</body>
</html>