<?php
include_once 'core/autoload.php';
include('template/header.php');

// Дефинирайте EXCLUDED_FILES, ако имате такива, които не искате да се показват
define('EXCLUDED_FILES', ['.', '..']);

echo '<div style="width: 92%; border: 1px solid black; margin: 0 auto;padding:15px;padding-top: 0px;margin-top: 20px;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;overflow: hidden;">';
echo '<h2>Downloads</h2>';

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
            'date' => filemtime($file_path)
        ];
    }
}

// Сортиране на файловете по дата на модификация (най-новите първи)
usort($file_details, function($a, $b) {
    return $b['date'] - $a['date'];
});

// Показване на таблицата с файлове
echo '<table border="1">';
echo "<tr><th>File</th><th>Size</th><th>Upload Date</th><th>Download</th></tr>";

foreach ($file_details as $index => $file) {
    $file_size_kb = round($file['size'] / 1024, 2);
    $file_date = date("d.m.Y H:i:s", $file['date']);
    $file_name = $file['name'];

    // Оцветяване на най-новия файл в зелено
    if ($index === 0) {
        echo "<tr style='background-color: lightgreen;'>";
    } else {
        echo "<tr>";
    }

    echo "<td>" . $file_name . "</td>";
    echo "<td>" . $file_size_kb . " KB</td>";
    echo "<td>" . $file_date . "</td>";
    echo "<td><a href='" . $dir . $file_name . "' download='" . $file_name . "'>Download</a></td>";
    echo "</tr>";
}

echo "</table>";
echo '</div><br />';
include('template/footer.php');
?>