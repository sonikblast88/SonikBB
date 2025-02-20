<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $target_dir = '../uploads/versions/'; // Път към папката с версиите
        $target_file = $target_dir . basename($_FILES["version_file"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Проверка за типа на файла (можете да добавите и други проверки)
        if ($fileType != "zip" && $fileType != "rar") { // Примерни типове файлове
            echo "Разрешени са само ZIP, RAR файлове.";
            $uploadOk = 0;
        }

        // Проверка за съществуващ файл със същото име
        if (file_exists($target_file)) {
            echo "Файл с това име вече съществува.";
            $uploadOk = 0;
        }

        // Проверка за размера на файла (например, 30MB)
        if ($_FILES["version_file"]["size"] > 30 * 1024 * 1024) {
            echo "Размерът на файла е твърде голям.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Файлът не беше качен.";
        } else {
            if (move_uploaded_file($_FILES["version_file"]["tmp_name"], $target_file)) {
                header("Location: ../downloads.php"); // Пренасочване към страницата с изтегляния
                exit();
            } else {
                echo "Грешка при качване на файла.";
            }
        }
    }
} else {
    echo "Нямате права да качвате файлове.";
}
?>