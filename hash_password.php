<?php
$password = '123'; // The plain text password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo $hashed_password; // Print the hashed password
?>
