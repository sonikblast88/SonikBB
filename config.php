<?php

// Database settings
define('DB_HOST', 'localhost'); // Database host
define('DB_USERNAME', 'root'); // Database username
define('DB_PASSWORD', ''); // Database password
define('DB_NAME', 'myforum'); // Database name

// Path to the forum
$path = './'; // or '/sonikbb/' or another value
define('WEBSITE', "https://sonikbb.eu/dev"); // do not add a slash at the end / | no / at the end |
define('WEBSITE_DESC', 'SonikBB Small and Lite Forum Written In PHP');

// Other settings
define('SITE_TITLE', 'SONIKBB DEV FORUMS'); // Site title
define('SITE_VERSION', '0.0.22 Dev'); // Site version
date_default_timezone_set('Europe/Sofia'); // Time zone

define('EXCLUDED_FILES', array('.htaccess', 'config.php', 'README.md', 'скрит_файл.zip', 'index.php', 'download_counts.txt')); // Translated file name

?>
