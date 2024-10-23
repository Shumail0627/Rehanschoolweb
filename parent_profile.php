<?php
session_start();
require 'db.php';

// Check if the user is logged in and is a Parent
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'Parent') {
    header('Location: login.php');
    exit();
}

$parent_id = $_SESSION['admin_id'];

// Fetch parent details
$parent_query = "SELECT * FROM users WHERE id = ? AND role = 'Parent'";
$stmt = $conn->prepare($parent_query);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$parent_result = $stmt->get_result();
$parent = $parent_result->fetch_assoc();

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $update_query = "UPDATE users SET name = ?, gmail = ?, phone = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssi", $name, $email, $phone, $address, $parent_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh parent data
            $stmt->execute();
            $parent_result = $stmt->get_result();
            $parent = $parent_result->fetch_assoc();
        } else {
            $error_message = "Error updating profile. Please try again.";
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        if (password_verify($current_password, $parent['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update_query = "UPDATE users SET password = ? WHERE id = ?";
                $password_update_stmt = $conn->prepare($password_update_query);
                $password_update_stmt->bind_param("si", $hashed_password, $parent_id);
                
                if ($password_update_stmt->execute()) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password. Please try again.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Profile - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .profile-info {
            margin-bottom: 20px;
        }
        .profile-info p {
            margin: 10px 0;
        }
        .profile-info strong {
            display: inline-block;
            width: 150px;
        }
        .edit-form, .password-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .password-input-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Parent Profile</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo $parent['name']; ?></p>
            <p><strong>Email:</strong> <?php echo $parent['gmail']; ?></p>
            <p><strong>Phone:</strong> <?php echo $parent['phone']; ?></p>
            <p><strong>Address:</strong> <?php echo $parent['address']; ?></p>
        </div>
        
        <h2>Edit Profile</h2>
        <form class="edit-form" method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $parent['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $parent['gmail']; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo $parent['phone']; ?>">
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo $parent['address']; ?>">
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
        
        <h2>Change Password</h2>
        <form class="password-form" method="POST" action="">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" required>
                    <i class="toggle-password fas fa-eye" onclick="togglePassword('current_password')"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" required>
                    <i class="toggle-password fas fa-eye" onclick="togglePassword('new_password')"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <i class="toggle-password fas fa-eye" onclick="togglePassword('confirm_password')"></i>
                </div>
            </div>
            <button type="submit" name="change_password">Change Password</button>
        </form>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
