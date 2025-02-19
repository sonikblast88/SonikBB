<?php
include 'functions.php';

if ($_SESSION['is_loged'] == true) {
    $uploadDir = 'uploads/'; // Папка за качените файлове

    if (!empty($_FILES['imageUpload'])) {
        $file = $_FILES['imageUpload'];
        $fileName = basename($file['name']);
        $randomName = md5(uniqid()) . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Генериране на случайно име
        $filePath = $uploadDir . $randomName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']; // Позволени файлови типове
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Актуализиране на аватара в базата данни
                $profile_id = (int)$_POST['profile_id'];
                $filePath = '/' . $filePath; // Пътят към файла

                run_q('UPDATE users SET avatar="' . $filePath . '" WHERE user_id="' . $profile_id . '"');
                echo $filePath; // Връщане на пътя към файла за AJAX
            } else {
                echo "Грешка при качване на файл. Проверете правата за достъп до директорията.";
            }
        } else {
            echo "Невалиден тип файл. Позволени типове: " . implode(", ", $allowedTypes);
        }
    } else {
        echo "Не е качен файл.";
    }
} else {
    redirect('index.php');
}
?>