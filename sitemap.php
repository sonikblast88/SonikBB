<?php
header("Content-Type: application/xml; charset=UTF-8");

include_once 'core/autoload.php';

$database = new Database();
$db = $database->connect();

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Add the homepage
echo '<url><loc>' . WEBSITE . '/</loc><priority>1.0</priority></url>';

// Fetch and add categories
$stmt = $db->query("SELECT cat_id FROM categories");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<url><loc>' . WEBSITE . '/topics.php?cat_id=' . $row['cat_id'] . '</loc><priority>0.8</priority></url>';
}

// Fetch and add topics
$stmt = $db->query("SELECT topic_id, date_added_topic FROM topics");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<url>';
    echo '<loc>' . WEBSITE . '/topic.php?topic_id=' . $row['topic_id'] . '</loc>';
    echo '<lastmod>' . date(DATE_W3C, strtotime($row['date_added_topic'])) . '</lastmod>';
    echo '<priority>0.6</priority>';
    echo '</url>';
}

echo '</urlset>';
?>
