<?php

require_once 'config.php'; // Включваме config.php, за да имаме достъп до константите

ini_set( 'session.cookie_httponly', 1 ); // Защита на бисквитките на сесията
session_start(); // Стартираме сесията

include 'run_q.php'; // Включваме функцията за работа с базата данни
include 'functions/redirect.php'; // Включваме функцията за пренасочване
include 'functions/Parsedown.php'; // Включваме библиотеката Parsedown за Markdown

// Други функции (ако има такива)

?>