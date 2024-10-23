<?php
session_start();
require 'db.php'; // Include the database connection

// Initialize variables
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch input data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $campus = mysqli_real_escape_string($conn, $_POST['campus'] ?? '');
    $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $doj = mysqli_real_escape_string($conn, $_POST['doj']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);

    // Additional details
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name'] ?? '');
    $father_age = mysqli_real_escape_string($conn, $_POST['father_age'] ?? '');
    $father_job = mysqli_real_escape_string($conn, $_POST['father_job'] ?? '');
    $father_whatsapp = mysqli_real_escape_string($conn, $_POST['father_whatsapp'] ?? '');
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name'] ?? '');
    $mother_age = mysqli_real_escape_string($conn, $_POST['mother_age'] ?? '');
    $mother_job = mysqli_real_escape_string($conn, $_POST['mother_job'] ?? '');
    $mother_whatsapp = mysqli_real_escape_string($conn, $_POST['mother_whatsapp'] ?? '');
    $number_of_siblings = mysqli_real_escape_string($conn, $_POST['number_of_siblings'] ?? '');
    $student_whatsapp = mysqli_real_escape_string($conn, $_POST['student_whatsapp'] ?? '');
    $class = mysqli_real_escape_string($conn, $_POST['class'] ?? '');
    $reason_for_joining = mysqli_real_escape_string($conn, $_POST['reason_for_joining'] ?? '');
    $favorite_food_dishes = mysqli_real_escape_string($conn, $_POST['favorite_food_dishes'] ?? '');
    $ideal_personalities = mysqli_real_escape_string($conn, $_POST['ideal_personalities'] ?? '');
    $plan_for_crore_rupees = mysqli_real_escape_string($conn, $_POST['plan_for_crore_rupees'] ?? '');
    $biggest_wish = mysqli_real_escape_string($conn, $_POST['biggest_wish'] ?? '');
    $vision_10_years = mysqli_real_escape_string($conn, $_POST['vision_10_years'] ?? '');

    // Handle picture upload
    $picturePath = 'uploads/default.jpg'; // Default to a placeholder image
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileExtension = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $newFileName = "user_" . $roll_no . "_" . time() . "." . $fileExtension;
        $targetFile = $targetDir . $newFileName;

        $allowedExtensions = array("jpg", "jpeg", "png", "gif");
        if (in_array($fileExtension, $allowedExtensions)) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
                $picturePath = $targetFile; // Update picture path if upload is successful
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Check if email already exists
    $sql = "SELECT * FROM users WHERE gmail = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $error = "Email already exists.";
    } else {
        // Validate campus for Principal, Vice-Principal, and Teacher roles
        if (($role == 'Principal' || $role == 'Vice-Principal' || $role == 'Teacher') && empty($campus)) {
            $error = "Campus is required for $role role.";
        } else {
            // Insert the new user into the users table
            $sql = "INSERT INTO users (
                name, gmail, password, role, campus, roll_no, status, dob, doj, city, country, age, picture_path, father_name, father_age, 
                father_job, father_whatsapp, mother_name, mother_age, mother_job, mother_whatsapp, 
                number_of_siblings, student_whatsapp, reason_for_joining, 
                favorite_food_dishes, ideal_personalities, plan_for_crore_rupees, biggest_wish, vision_10_years
            ) VALUES (
                '$name', '$email', '$password', '$role', '$campus', '$roll_no', '$status', '$dob', '$doj', '$city', '$country', '$age', '$picturePath', '$father_name', '$father_age', 
                '$father_job', '$father_whatsapp', '$mother_name', '$mother_age', '$mother_job', '$mother_whatsapp', 
                '$number_of_siblings', '$student_whatsapp', '$reason_for_joining', 
                '$favorite_food_dishes', '$ideal_personalities', '$plan_for_crore_rupees', '$biggest_wish', '$vision_10_years'
            )";

            if (mysqli_query($conn, $sql)) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'New $role has been added successfully!',
                        confirmButtonColor: '#007bff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'admin_dashboard.php';
                        }
                    });
                </script>";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <title>Add User - Rehan School</title>
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
            align-items: center;
        }

        .profile-picture {
            width: 100%;
            display: flex;
            justify-content: center;
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
            background-color: #ffc300;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .update-button {
            background-color: #ffc300;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }
            .profile-section,
            .edit-profile-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-picture">
                <img src="uploads/default.jpg" alt="Profile Picture" id="profileImg">
                <input type="file" id="uploadPic" name="picture" accept="image/*" style="display:none;" />
                <button type="button" class="upload-button" onclick="document.getElementById('uploadPic').click();">Upload New Photo</button>
                <p class="upload-instructions">Upload a Picture. Larger image will be resized automatically. Maximum upload size is 1 MB.</p>
            </div>
        </div>
        
        <!-- Edit Profile Section -->
        <div class="edit-profile-section">
            <h2>Add New User</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form class="edit-profile-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group hidden" id="rollNoField">
                    <label for="roll_no">Roll No</label>
                    <input type="text" id="roll_no" name="roll_no">
                </div>
                <div class="form-group">
                    <label for="role">Select Role</label>
                    <select id="role" name="role" required>
                        <option value="Admin">Admin</option>
                        <option value="Principal">Principal</option>
                        <option value="Vice-Principal">Vice-Principal</option> <!-- New option added -->
                        <option value="Teacher">Teacher</option>
                        <option value="Student">Student</option>
                    </select>
                </div>
                <div class="form-group hidden" id="campusField">
                    <label for="campus">Select Campus</label>
                    <select id="campus" name="campus">
                        <option value="Korangi">Korangi Campus</option>
                        <option value="Munawwar">Munawwar Campus</option>
                        <option value="Islamabad">Islamabad Campus</option>
                        <option value="Online">Online Academy</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                     <label for="age">Age</label>
                     <input type="number" id="age" name="age" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth (DOB)</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label for="doj">Date of Joining (DOJ)</label>
                    <input type="date" id="doj" name="doj" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" required>
                </div>

                <!-- Additional fields -->
                <div class="form-group" id="fatherNameField">
                    <label for="father_name">Father's Name</label>
                    <input type="text" id="father_name" name="father_name">
                </div>
                <div class="form-group" id="fatherAgeField">
                    <label for="father_age">Father's Age</label>
                    <input type="number" id="father_age" name="father_age">
                </div>
                <div class="form-group" id="fatherJobField">
                    <label for="father_job">Father's Job</label>
                    <input type="text" id="father_job" name="father_job">
                </div>
                <div class="form-group" id="fatherWhatsappField">
                    <label for="father_whatsapp">Father's WhatsApp</label>
                    <input type="text" id="father_whatsapp" name="father_whatsapp">
                </div>
                <div class="form-group" id="motherNameField">
                    <label for="mother_name">Mother's Name</label>
                    <input type="text" id="mother_name" name="mother_name">
                </div>
                <div class="form-group" id="motherAgeField">
                    <label for="mother_age">Mother's Age</label>
                    <input type="number" id="mother_age" name="mother_age">
                </div>
                <div class="form-group" id="motherJobField">
                    <label for="mother_job">Mother's Job</label>
                    <input type="text" id="mother_job" name="mother_job">
                </div>
                <div class="form-group" id="motherWhatsappField">
                    <label for="mother_whatsapp">Mother's WhatsApp</label>
                    <input type="text" id="mother_whatsapp" name="mother_whatsapp">
                </div>
                <div class="form-group" id="siblingsField">
                    <label for="number_of_siblings">Number of Siblings</label>
                    <input type="number" id="number_of_siblings" name="number_of_siblings">
                </div>
                <div class="form-group" id="studentWhatsappField">
                    <label for="student_whatsapp">Student's WhatsApp</label>
                    <input type="text" id="student_whatsapp" name="student_whatsapp">
                </div>
                <div class="form-group full-width" id="reasonForJoiningField">
                    <label for="reason_for_joining">Reason for Joining</label>
                    <textarea id="reason_for_joining" name="reason_for_joining"></textarea>
                </div>
                <div class="form-group full-width" id="favoriteFoodDishesField">
                    <label for="favorite_food_dishes">Favorite Food Dishes</label>
                    <input type="text" id="favorite_food_dishes" name="favorite_food_dishes">
                </div>
                <div class="form-group full-width" id="idealPersonalitiesField">
                    <label for="ideal_personalities">Ideal Personalities</label>
                    <input type="text" id="ideal_personalities" name="ideal_personalities">
                </div>
                <div class="form-group full-width" id="planForCroreRupeesField">
                    <label for="plan_for_crore_rupees">Plan for 1 Crore Rupees</label>
                    <textarea id="plan_for_crore_rupees" name="plan_for_crore_rupees"></textarea>
                </div>
                <div class="form-group full-width" id="biggestWishField">
                    <label for="biggest_wish">Biggest Wish</label>
                    <textarea id="biggest_wish" name="biggest_wish"></textarea>
                </div>
                <div class="form-group full-width" id="visionField">
                    <label for="vision_10_years">Vision for 10 Years Ahead</label>
                    <textarea id="vision_10_years" name="vision_10_years"></textarea>
                </div>
                <button type="submit" class="update-button">Add User</button>
            </form>
        </div>
    </div>

    <script>
        // Show/hide fields based on role selection
        document.getElementById('role').addEventListener('change', function() {
            var role = this.value;

            // Reset fields visibility
            document.getElementById('rollNoField').classList.add('hidden');
            document.getElementById('campusField').classList.add('hidden');

            // Show fields based on selected role
            if (role === 'Student') {
                document.getElementById('rollNoField').classList.remove('hidden');
                document.getElementById('campusField').classList.remove('hidden');
                showStudentSpecificFields(true);
            } else if (role === 'Principal' || role === 'Vice-Principal' || role === 'Teacher') { // Updated condition
                document.getElementById('campusField').classList.remove('hidden');
                showStudentSpecificFields(false);
            } else {
                showStudentSpecificFields(false);
            }
        });

        function showStudentSpecificFields(show) {
            var fields = [
                'fatherNameField', 'fatherAgeField', 'fatherJobField', 'fatherWhatsappField',
                'motherNameField', 'motherAgeField', 'motherJobField', 'motherWhatsappField',
                'siblingsField', 'studentWhatsappField', 'reasonForJoiningField', 'favoriteFoodDishesField',
                'idealPersonalitiesField', 'planForCroreRupeesField', 'biggestWishField', 'visionField'
            ];

            fields.forEach(function(fieldId) {
                document.getElementById(fieldId).classList.toggle('hidden', !show);
            });
        }
    </script>
</body>
</html>