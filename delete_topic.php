<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);
$commentsModel = new Comments($db);

// Проверка за администратор или автор на темата
if (!isset($_SESSION['user_id'])) {
    die("Нямате достъп до тази страница.");
}

$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Проверка дали потребителят е администратор или автор на темата
$topic = $topicsModel->getTopicById($topic_id);
if (!$topic) {
    die("Темата не е намерена.");
}

$isAdmin = $_SESSION['type'] == 2;
$isAuthor = $topic['topic_author'] == $_SESSION['user_id'];

if (!$isAdmin && !$isAuthor) {
    die("Нямате права да изтриете тази тема.");
}

// Изтриване на темата и коментарите
if ($topicsModel->deleteTopic($topic_id)) {
    header("Location: index.php"); // Пренасочване към началната страница
    exit();
} else {
    die("Грешка при изтриване на темата.");
}
?>