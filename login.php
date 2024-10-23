<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE gmail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            switch ($user['role']) {
                case 'Admin':
                    header('Location: admin_dashboard.php');
                    break;
                case 'Principal':
                    $_SESSION['campus'] = $user['campus'];
                    header('Location: principal_dashboard.php');
                    break;
                case 'Parent':
                    $_SESSION['parent_id'] = $user['id'];
                    header('Location: parent_dashboard.php');
                    break;
                case 'Vice-Principal':
                    $_SESSION['campus'] = $user['campus'];
                    header('Location: vice_principal_dashboard.php');
                    break;
                case 'Teacher':
                    $_SESSION['teacher_id'] = $user['id'];
                    $_SESSION['campus'] = $user['campus'];
                    header('Location: teacher_dashboard.php');
                    break;
                case 'Student':
                    $_SESSION['student_id'] = $user['id'];
                    header('Location: student_dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with this email.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rehan School</title>
    <link rel="icon" href="./images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            max-width: 400px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 80px;
            height: auto;
        }
        h1 {
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
            text-align: center;
            font-weight: 600;
        }
        label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.1s ease;
        }
        button:hover {
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .error {
            color: #e74c3c;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="./images/logo.png" alt="Rehan School Logo">
        </div>
        <h1>Welcome Back</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
            
            <button type="submit">Log In</button>
        </form>
    </div>
</body>
</html>