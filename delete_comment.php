<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$commentsModel = new Comments($db);

// Проверка дали потребителят е логнат
if (!isset($_SESSION['user_id'])) {
    die("Нямате достъп до тази страница.");
}

// Вземане на comment_id и topic_id от URL
$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Проверка за валидност на comment_id и topic_id
if ($comment_id <= 0 || $topic_id <= 0) {
    die("Невалиден идентификатор на коментар или тема.");
}

// Извличане на информация за коментара
$comment = $commentsModel->getCommentById($comment_id);
if (!$comment) {
    die("Коментарът не е намерен.");
}

// Проверка дали потребителят е администратор или автор на коментара
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 2; // Ако тип 2 е администратор
$isAuthor = $comment['comment_author'] == $_SESSION['user_id'];

if (!$isAdmin && !$isAuthor) {
    die("Нямате права да изтриете този коментар.");
}

// Изтриване на коментара
if ($commentsModel->deleteComment($comment_id)) {
    // Пренасочване обратно към темата
    header("Location: topic.php?topic_id=" . $topic_id);
    exit();
} else {
    die("Грешка при изтриване на коментара.");
}
?>