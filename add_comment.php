<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$commentsModel = new Comments($db);

// Вземане на ID на темата от URL
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Обработка на формата за добавяне на коментар
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];
    $comment_author = $_SESSION['user_id'];

    if ($commentsModel->addComment($topic_id, $comment, $comment_author)) {
        header("Location: topic.php?topic_id=" . $topic_id);
        exit();
    } else {
        echo "Грешка при добавяне на коментар.";
    }
}

include 'template/header.php';
?>

<form method="POST" action="add_comment.php?topic_id=<?= $topic_id ?>">
    <textarea name="comment" placeholder="Your comment" required></textarea>
    <button type="submit">Post Comment</button>
    <a href="topic.php?topic_id=<?= $topic_id ?>"><button type="button">Cancel</button></a>
</form>

<?php
include 'template/footer.php';
?>