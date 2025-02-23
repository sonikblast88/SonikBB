<?php
include 'functions.php';

if ($_SESSION['is_loged'] == true) {
    $uploadDir = 'uploads/'; // Directory for uploaded files

    if (!empty($_FILES['imageUpload'])) {
        $file = $_FILES['imageUpload'];
        $fileName = basename($file['name']);
        $randomName = md5(uniqid()) . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Generate random name
        $filePath = $uploadDir . $randomName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file types
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Update avatar in the database
                $profile_id = (int)$_POST['profile_id'];
                $filePath = $filePath; // The path to the file

                run_q('UPDATE users SET avatar="' . $filePath . '" WHERE user_id="' . $profile_id . '"');
                echo $filePath; // Return the file path for AJAX
            } else {
                echo "Error uploading file. Check directory permissions."; // Translated message
            }
        } else {
            echo "Invalid file type. Allowed types: " . implode(", ", $allowedTypes); // Translated message
        }
    } else {
        echo "No file uploaded."; // Translated message
    }
} else {
    redirect('../index.php');
}
?>