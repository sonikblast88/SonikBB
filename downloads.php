<?php
session_start();
include_once 'core/autoload.php';
include('template/header.php');

echo '<div style="width: 92%; border: 1px solid black; margin: 0 auto;padding:15px;padding-top: 0px;margin-top: 20px;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;overflow: hidden;">';
echo '<h2>Downloads</h2>';

$download_counts_file = __DIR__ . '/uploads/versions/download_counts.txt';

// Създаване на файла, ако не съществува
if (!file_exists($download_counts_file)) {
    file_put_contents($download_counts_file, '');
}

// Зареждане на броячите за изтегляне от файла
$download_counts = [];
$file_content = file_get_contents($download_counts_file);
if ($file_content) {
    $lines = explode("\n", $file_content);
    foreach ($lines as $line) {
        $parts = explode(":", $line);
        if (count($parts) == 2) {
            $filename = trim($parts[0]);
            $count = intval(trim($parts[1]));
            $download_counts[$filename] = $count;
        }
    }
}

// Функция за увеличаване на броя на изтеглянията
function increment_download_count($filename) {
    global $download_counts, $download_counts_file;
    $download_counts[$filename] = isset($download_counts[$filename]) ? $download_counts[$filename] + 1 : 1;
    $new_content = "";
    foreach ($download_counts as $file => $count) {
        $new_content .= $file . ":" . $count . "\n";
    }
    file_put_contents($download_counts_file, $new_content);
}

// Проверка дали е направена заявка за изтегляне
if (isset($_GET['download'])) {
    $filename = $_GET['download'];
    $file_path = __DIR__ . '/uploads/versions/' . $filename;

    if (file_exists($file_path) && !in_array($filename, EXCLUDED_FILES)) {
        increment_download_count($filename);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        echo "Файлът не е намерен или не е разрешен за изтегляне.";
    }
}

// Получаване на всички файлове от директорията и сортиране по дата на модификация
$dir = __DIR__ . '/uploads/versions/';
$files = scandir($dir);
$file_details = [];

foreach ($files as $file) {
    if (!in_array($file, EXCLUDED_FILES) && is_file($dir . $file)) {
        $file_path = $dir . $file;
        $file_details[] = [
            'name' => $file,
            'size' => filesize($file_path),
            'date' => filemtime($file_path),
            'download_count' => isset($download_counts[$file]) ? $download_counts[$file] : 0
        ];
    }
}

// Сортиране на файловете по дата на модификация (най-новите първи)
usort($file_details, function($a, $b) {
    return $b['date'] - $a['date'];
});

// Показване на таблицата с файлове
echo '<table border="1">';
echo "<tr><th>File</th><th>Size</th><th>Upload Date</th><th>Downloads</th><th>Download</th></tr>";

foreach ($file_details as $index => $file) {
    $file_size_kb = round($file['size'] / 1024, 2);
    $file_date = date("d.m.Y H:i:s", $file['date']);
    $file_name = $file['name'];
    $download_count = $file['download_count'];

    // Оцветяване на най-новия файл в зелено
    if ($index === 0) {
        echo "<tr style='background-color: lightgreen;'>";
    } else {
        echo "<tr>";
    }

    echo "<td>" . $file_name . "</td>";
    echo "<td>" . $file_size_kb . " KB</td>";
    echo "<td>" . $file_date . "</td>";
    echo "<td>" . $download_count . "</td>";
    echo "<td><a href='?download=" . $file_name . "'>Download</a></td>";
    echo "</tr>";
}

echo "</table>";

// admin check
$is_admin = isAdmin();

if ($is_admin) {
    if (isset($_FILES['version_file'])) {
        $target_dir = __DIR__ . '/uploads/versions/';
        $target_file = $target_dir . basename($_FILES['version_file']['name']);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Проверка дали файлът вече съществува
        if (file_exists($target_file)) {
            echo "Файлът вече съществува.";
            $uploadOk = 0;
        }

        // Проверка на размера на файла
        if ($_FILES['version_file']['size'] > 50000000) { // 50MB
            echo "Файлът е твърде голям.";
            $uploadOk = 0;
        }

        // Разрешени файлови формати
        $allowed_types = array("zip", "rar", "exe", "pdf", "txt", "doc", "docx");
        if (!in_array($fileType, $allowed_types)) {
            echo "Разрешени са само ZIP, RAR, EXE, PDF, TXT, DOC и DOCX файлове.";
            $uploadOk = 0;
        }

        // Проверка дали $uploadOk е 0 заради грешка
        if ($uploadOk == 0) {
            echo "Файлът не беше качен.";
        } else {
            if (move_uploaded_file($_FILES['version_file']['tmp_name'], $target_file)) {
                echo "Файлът " . htmlspecialchars(basename($_FILES['version_file']['name'])) . " беше качен успешно.";
            } else {
                echo "Възникна грешка при качването на файла.";
            }
        }
    }

    echo '<h3>Upload New Version</h3>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="version_file" required><br><br>';
    echo '<input type="submit" value="Upload">';
    echo '</form>';
}

echo '</div><br />';
include('template/footer.php');
?>