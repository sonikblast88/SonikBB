<?php
session_start();

if (isset($_SESSION['is_loged']) && $_SESSION['is_loged'] == true) {
    $uploadDir = 'uploads/';

    // Check if the upload directory exists and is writable
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        echo "Error: Upload directory does not exist or is not writable.";
        exit; // Stop execution to prevent further errors
    }

    if (!empty($_FILES['imageUpload'])) {
        $file = $_FILES['imageUpload'];
        $fileName = basename($file['name']);

        // Generate a robust random name (using openssl_random_pseudo_bytes if available)
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomBytes = openssl_random_pseudo_bytes(16);
            $randomName = bin2hex($randomBytes) . "." . pathinfo($fileName, PATHINFO_EXTENSION);
        } else {
            // Fallback if openssl is not available
            $randomName = md5(uniqid(rand(), true)) . "." . pathinfo($fileName, PATHINFO_EXTENSION);
        }

        $filePath = $uploadDir . $randomName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file size (max 5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5MB (adjust as needed)
        if ($file['size'] > $maxFileSize) {
            echo "The file size exceeds the maximum allowed size of " . ($maxFileSize / (1024 * 1024)) . "MB.";
            exit;
        }

        if (in_array($fileType, $allowedTypes)) {
            // Securely move the uploaded file (suppress warnings with @)
            if (@move_uploaded_file($file['tmp_name'], $filePath)) {
                echo $filePath; // Return the path relative to the web root
            } else {
                // Detailed error handling
                $error = error_get_last();
                echo "Error uploading file: " . (isset($error['message']) ? $error['message'] : "Unknown error.");
            }
        } else {
            echo "Invalid file type. Allowed types: " . implode(", ", $allowedTypes);
        }
    } else {
        echo "No file uploaded.";
    }
} else {
    // If the user is not logged in, display an error message or redirect
    echo "You do not have access to this page.";
    exit;
}
?>
