<?php
session_start();
require 'db.php'; // Include your database connection

// Ensure only logged-in users can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $campus = $_POST['campus'];

    $sql = "INSERT INTO users (name, email, password, role, campus) VALUES ('$name', '$email', '$password', '$role', '$campus')";
    if (mysqli_query($conn, $sql)) {
        header('Location: manage_users.php?status=success');
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>
