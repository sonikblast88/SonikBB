<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$commentsModel = new Comments($db);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You do not have access to this page.");
}

// Retrieve comment_id and topic_id from URL
$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Validate comment_id and topic_id
if ($comment_id <= 0 || $topic_id <= 0) {
    die("Invalid comment or topic identifier.");
}

// Retrieve comment information
$comment = $commentsModel->getCommentById($comment_id);
if (!$comment) {
    die("Comment not found.");
}

// Check if the user is an administrator or the author of the comment
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 2; // Type 2 is administrator
$isAuthor = $comment['comment_author'] == $_SESSION['user_id'];

if (!$isAdmin && !$isAuthor) {
    die("You do not have permission to delete this comment.");
}

// Delete the comment
if ($commentsModel->deleteComment($comment_id)) {
    // Redirect back to the topic page
    header("Location: topic.php?topic_id=" . $topic_id);
    exit();
} else {
    die("Error deleting comment.");
}
?>
