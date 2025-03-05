<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';
include_once 'models/Category.php';
include_once 'models/Users.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);
$categoryModel = new Category($db);
$usersModel = new Users($db);
$commentsModel = new Comments($db);
$parsedown = new Parsedown();

$is_admin = isAdmin();
$isUserOrAdmin = isUserOrAdmin();

// Retrieve topic ID
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$topic = $topicsModel->getTopicById($topic_id);

if (!$topic) {
    echo "Topic not found!";
    exit();
}

$cat_id = $topic['parent'];
$user = $usersModel->getUserById($topic['topic_author']);
$comments = $commentsModel->getCommentsByTopicId($topic_id);

// **Generate keywords dynamically and store them globally**
$GLOBALS['keywords'] = $topicsModel->generateKeywords($topic_id);

// **Now include the header AFTER generating keywords**
include_once 'template/header.php';

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_author = $_SESSION['user_id'];

    if (isset($_POST['add_comment'])) {
        if ($commentsModel->addComment($topic_id, $_POST['comment'], $comment_author)) {
            header("Location: topic.php?topic_id=" . $topic_id);
            exit();
        }
    } elseif (isset($_POST['edit_comment'])) {
        $comment_id = $_POST['comment_id'];
        $comment = $_POST['comment'];
        $commentData = $commentsModel->getCommentById($comment_id);

        if ($_SESSION['type'] == 2 || $commentData['comment_author'] == $_SESSION['user_id']) {
            if ($commentsModel->updateComment($comment_id, $comment)) {
                header("Location: topic.php?topic_id=" . $topic_id);
                exit();
            }
        }
    } elseif (isset($_POST['delete_comment'])) {
        $comment_id = $_POST['comment_id'];
        $commentData = $commentsModel->getCommentById($comment_id);

        if ($_SESSION['type'] == 2 || $commentData['comment_author'] == $_SESSION['user_id']) {
            if ($commentsModel->deleteComment($comment_id)) {
                header("Location: topic.php?topic_id=" . $topic_id);
                exit();
            }
        }
    }
}
?>

<link rel="stylesheet" href="template/styles.css">

<a href="topics.php?cat_id=<?= $cat_id ?>" class="back-link"><img src="template/images/back.png" alt="" /></a>

<div class="topic-container">
    <h2><?= htmlspecialchars($topic['topic_name']) ?></h2>

    <!-- PROFILE PART START -->
    <div class="profile-card">
        <div class="profile-info">
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar" class="profile-image" />
            <div class="profile-text">
                <div class="username">👤 <?= htmlspecialchars($user['username']) ?></div>
                <div class="role <?= $user['type'] == 2 ? 'admin' : 'user' ?>">
                    <?= $user['type'] == 2 ? '🔴 Admin' : '🔵 User' ?>
                </div>
                <div>✍️ <?= htmlspecialchars($user['signature']) ?></div>
                <div>⏳ Last active: <?= date('d M Y \a\t H:i', strtotime($user['last_login'])) ?></div>
                <div>📅 Created: <?= date('d M Y \a\t H:i', strtotime($topic['date_added_topic'])) ?></div>
				<div>👀 Viewed: <?= $topicsModel->getVisitCountByTopicId($topic_id) ?> times</div>
                <div>✉️ <a href="mailto:<?= htmlspecialchars($user['email']) ?>">Send Email</a></div>
            </div>
        </div>

        <?php if ($is_admin || (isset($_SESSION['user_id']) && $topic['topic_author'] == $_SESSION['user_id'])): ?>
            <div class="btn-group">
                <a href="edit_topic.php?topic_id=<?= $topic_id ?>"><button>Edit</button></a>
                <a href="delete_topic.php?topic_id=<?= $topic_id ?>" onclick="return confirm('Are you sure?')"><button class="btn-delete">Delete</button></a>
            </div>
        <?php endif; ?>
    </div>
    <!-- PROFILE PART END -->

    <p><?= $parsedown->text($topic['topic_desc']) ?></p>
</div>

<?php if ($isUserOrAdmin): ?>
    <center><a href="add_comment.php?topic_id=<?= $topic_id ?>"><img src="template/images/comment.png" alt="" /></a></center>
<?php else: ?>
    <center>If you want to comment: <a href="login.php">Login</a> or <a href="register.php">Register</a></center>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
    <?php $commentAuthor = $usersModel->getUserById($comment['comment_author']); ?>
    <div class="comment-container">
        <div class="profile-card">
            <div class="profile-info">
                <img src="<?= htmlspecialchars($commentAuthor['avatar']) ?>" alt="User Avatar" class="profile-image" />
                <div class="profile-text">
                    <div class="username">👤 <?= htmlspecialchars($commentAuthor['username']) ?></div>
                    <div class="role <?= $commentAuthor['type'] == 2 ? 'admin' : 'user' ?>">
                        <?= $commentAuthor['type'] == 2 ? '🔴 Admin' : '🔵 User' ?>
                    </div>
                    <div>✍️ <?= htmlspecialchars($commentAuthor['signature']) ?></div>
                    <div>⏳ Last active: <?= date('d M Y \a\t H:i', strtotime($commentAuthor['last_login'])) ?></div>
                    <div>📅 Commented: <?= date('d M Y \a\t H:i', strtotime($comment['date_added_comment'])) ?></div>
                    <div>✉️ <a href="mailto:<?= htmlspecialchars($commentAuthor['email']) ?>">Send Email</a></div>
                </div>
            </div>

			<?php if ($is_admin || (isset($_SESSION['user_id']) && $comment['comment_author'] == $_SESSION['user_id'])): ?>
                <div class="btn-group">
                    <a href="edit_comment.php?comment_id=<?= $comment['comment_id'] ?>&topic_id=<?= $topic_id ?>"><button>Edit</button></a>
                    <a href="delete_comment.php?comment_id=<?= $comment['comment_id'] ?>&topic_id=<?= $topic_id ?>" onclick="return confirm('Are you sure?')">
                        <button class="btn-delete">Delete</button>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <p><?= $parsedown->text($comment['comment']) ?></p>
    </div>
<?php endforeach; ?>

<?php require_once('template/footer.php'); ?>
