<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $target_dir = '../uploads/versions/'; // Path to the versions folder
        $target_file = $target_dir . basename($_FILES["version_file"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type (you can add more checks)
        if ($fileType != "zip" && $fileType != "rar") { // Example file types
            echo "Only ZIP and RAR files are allowed."; 
            $uploadOk = 0;
        }

        // Check if a file with the same name already exists
        if (file_exists($target_file)) {
            echo "A file with that name already exists."; 
            $uploadOk = 0;
        }

        // Check file size (e.g., 30MB)
        if ($_FILES["version_file"]["size"] > 30 * 1024 * 1024) {
            echo "The file size is too large."; 
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "The file was not uploaded."; 
        } else {
            if (move_uploaded_file($_FILES["version_file"]["tmp_name"], $target_file)) {
                header("Location: ../downloads.php"); // Redirect to the downloads page
                exit();
            } else {
            echo "Error uploading the file."; 
            }
        }
    }
} else {
    echo "You do not have permission to upload files."; 
}
?>
