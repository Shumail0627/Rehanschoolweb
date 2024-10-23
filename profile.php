<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and is a student
if (!isset($_SESSION['student_id']) || $_SESSION['role'] != 'Student') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$success = '';
$error = '';
$studentId = $_SESSION['student_id'];

// Fetch student details from the database
$sql = "SELECT * FROM users WHERE id = '$studentId' AND role = 'Student'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);

// Update student details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $name = $_POST['name'];
    
    // If the password is being changed
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $updateQuery = "UPDATE users SET gmail = '$email', name = '$name', password = '$password' WHERE id = '$studentId'";
    } else {
        $updateQuery = "UPDATE users SET gmail = '$email', name = '$name' WHERE id = '$studentId'";
    }

    if (mysqli_query($conn, $updateQuery)) {
        $success = "Profile updated successfully!";
        // Update session name if the name is changed
        $_SESSION['admin_name'] = $name;
    } else {
        $error = "Error updating profile: " . mysqli_error($conn);
    }
}

// Handle profile picture upload
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
            $updatePictureQuery = "UPDATE users SET picture_path = '$targetFile' WHERE id = '$studentId'";
            if (mysqli_query($conn, $updatePictureQuery)) {
                $success = "Profile picture updated successfully!";
                $student['picture_path'] = $targetFile; // Update the current session data
            } else {
                $error = "Error updating profile picture in the database.";
            }
        } else {
            $error = "Error uploading file.";
        }
    } else {
        $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .profile-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }
        label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }
        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .success {
            color: green;
            margin-bottom: 20px;
            text-align: center;
        }
        .error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-picture {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-picture img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #007bff;
        }
        .upload-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .upload-instructions {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Profile</h1>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <div class="profile-picture">
            <img src="<?php echo $student['picture_path'] ? $student['picture_path'] : 'path/to/default/image.jpg'; ?>" alt="Profile Picture" id="profileImg">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="file" id="uploadPic" name="picture" accept="image/*" style="display:none;" />
                <button type="button" class="upload-button" onclick="document.getElementById('uploadPic').click();">Upload New Photo</button>
                <p class="upload-instructions">Upload a Picture. Larger image will be resized automatically. Maximum upload size is 1 MB.</p>
                <button type="submit" class="upload-button" style="display:none;" id="submitPicture">Update Picture</button>
            </form>
        </div>

        <form method="POST" action="">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['gmail']); ?>" required>

            <label for="password">New Password (leave blank to keep current):</label>
            <input type="password" id="password" name="password">

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <script>
        document.getElementById('uploadPic').addEventListener('change', function() {
            const [file] = this.files;
            if (file) {
                document.getElementById('profileImg').src = URL.createObjectURL(file);
                document.getElementById('submitPicture').style.display = 'inline-block';
            }
        });
    </script>
</body>
</html>
