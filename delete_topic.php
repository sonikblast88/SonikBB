<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);
$commentsModel = new Comments($db);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You do not have access to this page.");
}

$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Retrieve topic information
$topic = $topicsModel->getTopicById($topic_id);
if (!$topic) {
    die("Topic not found.");
}

// Check if the user is an administrator or the author of the topic
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 2;
$isAuthor = $topic['topic_author'] == $_SESSION['user_id'];

if (!$isAdmin && !$isAuthor) {
    die("You do not have permission to delete this topic.");
}

// Delete the topic along with its associated comments
if ($topicsModel->deleteTopic($topic_id)) {
    // Redirect to the homepage
    header("Location: index.php");
    exit();
} else {
    die("Error deleting topic.");
}
?>
