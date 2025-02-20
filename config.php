<?php

// Настройки за базата данни
define('DB_HOST', 'localhost'); // Хост на базата данни
define('DB_USERNAME', 'root'); // Потребителско име за базата данни
define('DB_PASSWORD', ''); // Парола за базата данни
define('DB_NAME', 'myforum'); // Име на базата данни

// Път до форума
$path = './'; // или '/sonikbb/' или друга стойност
define('WEBSITE', "https://sonikbb.eu/dev"); // do not add a slash at the end / | no / at the end |

// Други настройки
define('SITE_TITLE', 'SONIKBB DEV FORUMS'); // Заглавие на сайта
define('SITE_VERSION', '0.0.20 Dev'); // Версия на сайта
date_default_timezone_set('Europe/Sofia'); // часова зона

define('EXCLUDED_FILES', array('.htaccess', 'config.php', 'README.md', 'скрит_файл.zip'));

?>