<?php
include 'functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['is_loged'] == true) {
    $uploadDir = 'uploads/';

    // Check if the directory exists and is writable
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        echo "Грешка: Директорията за качване не съществува или няма права за запис.";
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
            echo "Размерът на файла надвишава максимално позволения размер от " . ($maxFileSize / (1024 * 1024)) . "MB.";
            exit;
        }

        if (in_array($fileType, $allowedTypes)) {
            // Use move_uploaded_file securely (check for errors)
            if (@move_uploaded_file($file['tmp_name'], $filePath)) { // Suppress warnings with @
                echo $filePath; // Return the path relative to the web root
            } else {
                // More specific error handling
                $error = error_get_last();
                echo "Грешка при качване на файл: " . (isset($error['message']) ? $error['message'] : "Неизвестна грешка.");
            }
        } else {
            echo "Невалиден тип файл. Позволени типове: " . implode(", ", $allowedTypes);
        }
    } else {
        echo "Не е качен файл.";
    }
} else {
    // Redirect or display an error message
    echo "Нямате достъп до тази страница."; // Or redirect('index.php');
    exit;
}
?>