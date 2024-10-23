<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            echo json_encode(["status" => "error", "message" => "Error: Please select a valid file format."]);
            exit();
        }

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            echo json_encode(["status" => "error", "message" => "Error: File size is larger than the allowed limit."]);
            exit();
        }

        // Verify MYME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . uniqid() . "_" . basename($filename);

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Update the database with the new picture path
                $query = "UPDATE users SET picture_path = '$target_file' WHERE id = '$studentId'";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    echo json_encode(["status" => "success", "message" => "Profile picture updated successfully", "picture_path" => $target_file]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to update database: " . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Sorry, there was an error uploading your file."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error: There was a problem uploading your file. Please try again."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error: No file uploaded"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>