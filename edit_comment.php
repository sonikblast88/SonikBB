<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$commentsModel = new Comments($db);

// Вземане на ID на коментара от URL
$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Извличане на информация за коментара
$commentData = $commentsModel->getCommentById($comment_id);

// Проверка дали потребителят има права да редактира коментара
if ($_SESSION['type'] != 2 && $commentData['comment_author'] != $_SESSION['user_id']) {
    echo "Нямате права да редактирате този коментар.";
    exit;
}

// Обработка на формата за редактиране на коментар
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];

    if ($commentsModel->updateComment($comment_id, $comment)) {
        header("Location: topic.php?topic_id=" . $topic_id);
        exit();
    } else {
        echo "Грешка при редактиране на коментар.";
    }
}

include 'template/header.php';
?>

<form method="POST" action="edit_comment.php?comment_id=<?= $comment_id ?>&topic_id=<?= $topic_id ?>">
    <textarea name="comment" required><?= $commentData['comment'] ?></textarea>
    <button type="submit">Save Changes</button>
    <a href="topic.php?topic_id=<?= $topic_id ?>"><button type="button">Cancel</button></a>
</form>

<?php
include 'template/footer.php';
?>