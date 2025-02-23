<?php
session_start();

if (isset($_SESSION['is_loged']) && $_SESSION['is_loged'] == true) {
    $uploadDir = 'uploads/';

    // Check if the directory exists and is writable
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        echo "Error: Upload directory does not exist or is not writable."; // Translated
        exit; // Stop execution to prevent further errors
    }

    if (!empty($_FILES['imageUpload'])) {
        $file = $_FILES['imageUpload'];
        $fileName = basename($file['name']);

        // Generate a more robust random name (using openssl_random_pseudo_bytes if available)
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomBytes = openssl_random_pseudo_bytes(16);
            $randomName = bin2hex($randomBytes) . "." . pathinfo($fileName, PATHINFO_EXTENSION);
        } else {
            $randomName = md5(uniqid(rand(), true)) . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Fallback if openssl is not available
        }

        $filePath = $uploadDir . $randomName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file size (add this!)
        $maxFileSize = 5 * 1024 * 1024; // 5MB (adjust as needed)
        if ($file['size'] > $maxFileSize) {
            echo "The file size exceeds the maximum allowed size of " . ($maxFileSize / (1024 * 1024)) . "MB."; // Translated
            exit;
        }

        if (in_array($fileType, $allowedTypes)) {
            // Use move_uploaded_file securely (check for errors)
            if (@move_uploaded_file($file['tmp_name'], $filePath)) { // Suppress warnings with @
                echo $filePath; // Return the path relative to the web root
            } else {
                // More specific error handling
                $error = error_get_last();
                echo "Error uploading file: " . (isset($error['message']) ? $error['message'] : "Unknown error."); // Translated
            }
        } else {
            echo "Invalid file type. Allowed types: " . implode(", ", $allowedTypes); // Translated
        }
    } else {
        echo "No file uploaded."; // Translated
    }
} else {
    // Redirect or display an error message
    echo "You do not have access to this page."; // Translated or redirect('index.php');
    exit;
}
?>