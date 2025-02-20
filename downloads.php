<?php
include 'functions.php';
include 'template/header.php';

echo '<div id="content">';
echo '<h2>Изтегляния</h2>';

$versions_dir = 'uploads/versions/';

if (is_dir($versions_dir)) {
    $files = scandir($versions_dir);

	$versions = array_filter($files, function ($file) use ($versions_dir) {
		return is_file($versions_dir . $file) && !in_array($file, EXCLUDED_FILES) && substr($file, 0, 1) !== '.';
	});

    if (!empty($versions)) {
        echo '<table border="1">';
        echo '<tr><th>Файл</th><th>Размер</th><th>Дата на качване</th><th>Изтегляне</th></tr>';

        foreach ($versions as $version) {
            $file_path = $versions_dir . $version;
            $file_size = filesize($file_path);
            $file_size_kb = round($file_size / 1024, 2);
            $file_date = date("d.m.Y H:i:s", filemtime($file_path));

            echo '<tr>';
            echo '<td>' . htmlspecialchars($version, ENT_QUOTES) . '</td>';
            echo '<td>' . $file_size_kb . ' KB</td>';
            echo '<td>' . $file_date . '</td>';
            echo '<td><a href="' . $file_path . '">Изтегли</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Няма налични версии за изтегляне.</p>';
    }
} else {
    echo '<p>Папката с версиите не съществува.</p>';
}

// Форма за качване на нова версия (само за администратори)
if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    echo '<h3>Качи нова версия</h3>';
    echo '<form action="operations/upload_version.php" method="post" enctype="multipart/form-data">'; // Създайте upload_version.php
    echo '<input type="file" name="version_file" required><br><br>';
    echo '<input type="submit" value="Качи">';
    echo '</form>';
}

echo '</div>';
include 'aside.php';
include 'template/footer.php';
?>