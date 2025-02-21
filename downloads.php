<?php
include 'functions.php'; // Include functions.php for $_SESSION and $user_info
include 'template/header.php'; // Include header.php for design

echo '<div id="content">'; // Start of div with id "content"
echo '<h2>Downloads</h2>';

$download_counts_file = __DIR__ . '/uploads/versions/download_counts.txt';

// Create the file if it doesn't exist
if (!file_exists($download_counts_file)) {
    file_put_contents($download_counts_file, '');
}

// Load the download counts from the file
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

// Function to increment the download count
function increment_download_count($filename) {
    global $download_counts, $download_counts_file;
    $download_counts[$filename] = isset($download_counts[$filename]) ? $download_counts[$filename] + 1 : 1;
    $new_content = "";
    foreach ($download_counts as $file => $count) {
        $new_content .= $file . ":" . $count . "\n";
    }
    file_put_contents($download_counts_file, $new_content);
}

// Check if a download request has been made
if (isset($_GET['download'])) {
    $filename = $_GET['download'];
    $file_path = __DIR__ . '/uploads/versions/' . $filename;

    if (file_exists($file_path) && !in_array($filename, EXCLUDED_FILES)) { // Check if the file exists and is not in the excluded list
        increment_download_count($filename);

        // Send HTTP headers to instruct the browser
        header('Content-Type: application/octet-stream'); // File type (can be changed as needed)
        header('Content-Disposition: attachment; filename="' . $filename . '"'); // Set the filename for download
        header('Content-Length: ' . filesize($file_path)); // Set the file size
        readfile($file_path); // Send the file to the browser
        exit; // Stop script execution after download
    } else {
        echo "File not found or not allowed for download.";
    }
}

// Get all files and sort them by modification date
$files = scandir(__DIR__ . '/uploads/versions');
$file_details = [];
foreach ($files as $file) {
    if ($file != "." && $file != ".." && is_file(__DIR__ . '/uploads/versions/' . $file) && !in_array($file, EXCLUDED_FILES)) {
        $file_path = __DIR__ . '/uploads/versions/' . $file;
        $file_details[] = [
            'name' => $file,
            'size' => filesize($file_path),
            'date' => filemtime($file_path),
            'download_count' => isset($download_counts[$file]) ? $download_counts[$file] : 0
        ];
    }
}

// Sort files by date (newest first)
usort($file_details, function($a, $b) {
    return $b['date'] - $a['date'];
});

// Display the table of files and download counts
echo '<table border="1">';
echo "<tr><th>File</th><th>Size</th><th>Upload Date</th><th>Downloads</th><th>Download</th></tr>";

foreach ($file_details as $index => $file) {
    $file_size_kb = round($file['size'] / 1024, 2);
    $file_date = date("d.m.Y H:i:s", $file['date']);
    $download_count = $file['download_count'];
    $file_name = $file['name'];

    // Highlight the newest file in green
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

// Form for uploading a new version (only for administrators)
if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    echo '<h3>Upload New Version</h3>';
    echo '<form action="operations/upload_version.php" method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="version_file" required><br><br>';
    echo '<input type="submit" value="Upload">';
    echo '</form>';
}

echo '</div>'; // End of div with id "content"
include 'aside.php'; // Include aside.php for the sidebar
include 'template/footer.php'; // Include footer.php for the footer
?>
