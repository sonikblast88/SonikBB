<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';
include_once 'models/Category.php';
include_once 'models/Users.php';
include_once 'models/Comments.php';
include_once 'template/header.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);
$categoryModel = new Category($db);
$usersModel = new Users($db);
$commentsModel = new Comments($db);
$parsedown = new Parsedown();

// Check if user is admin
$is_admin = isAdmin();

// Check if user is either admin or a regular user
$isUserOrAdmin = isUserOrAdmin();

// Retrieve topic ID from URL
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Retrieve topic information
$topic = $topicsModel->getTopicById($topic_id);
$cat_id = $topic['parent'];

// Retrieve information for the user who created the topic
$user = $usersModel->getUserById($topic['topic_author']);

// Retrieve comments for the topic
$comments = $commentsModel->getCommentsByTopicId($topic_id);

// Process the form for adding/editing/deleting comments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_comment'])) {
        $comment = $_POST['comment'];
        $comment_author = $_SESSION['user_id']; // Current user

        if ($commentsModel->addComment($topic_id, $comment, $comment_author)) {
            header("Location: topic.php?topic_id=" . $topic_id);
            exit();
        }
    } elseif (isset($_POST['edit_comment'])) {
        // Edit comment
        $comment_id = $_POST['comment_id'];
        $comment = $_POST['comment'];

        // Check if the user has permission to edit the comment
        $commentData = $commentsModel->getCommentById($comment_id);
        if ($_SESSION['type'] == 2 || $commentData['comment_author'] == $_SESSION['user_id']) {
            if ($commentsModel->updateComment($comment_id, $comment)) {
                header("Location: topic.php?topic_id=" . $topic_id);
                exit();
            }
        }
    } elseif (isset($_POST['delete_comment'])) {
        // Delete comment
        $comment_id = $_POST['comment_id'];

        // Check if the user has permission to delete the comment
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
<br>
<a href="topics.php?cat_id=<?= $cat_id ?>" style="display:block; text-align:center; margin-top:20px;">&larr; Back to Topics</a>

<div style="width: 92%; border: 1px solid black; margin: 0 auto; padding:15px; padding-top: 0px; margin-top: 20px; box-shadow: 0 0 8px rgba(0, 0, 0, .8); border-radius: 5px; overflow: hidden;">
    <h2><?= htmlspecialchars($topic['topic_name']) ?></h2>

    <!-- PROFILE PART START -->
    <div style="width: 99%; border: 1px solid black; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 0 8px rgba(0, 0, 0, .8); border-radius: 5px;">
        <div style="display: flex; align-items: center;">
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="" id="post-profile-image" style="margin-right: 10px; max-width: 150px; max-height: 150px; margin-left:12px;" />
            <div>
                <div>👤 Username: <?= htmlspecialchars($user['username']) ?></div>
                <div><?= $user['type'] == 2 ? '🔴 Admin' : '🔵 User' ?></div>
                <div>✍️ Signature: <?= htmlspecialchars($user['signature']) ?></div>
                <div>⏳ Last active: <?= date('d M Y \a\t H:i', strtotime($user['last_login'])) ?></div>
                <div>📅 Created: <?= date('d M Y \a\t H:i', strtotime($topic['date_added_topic'])) ?></div>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; padding-right: 10px;">
            <?php
            $isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 2;
            $isTopicAuthor = false; // Initialization
            if (isset($_SESSION['user_id'])) {
                $isTopicAuthor = $topic['topic_author'] == $_SESSION['user_id'];
            }

            if ($isAdmin || $isTopicAuthor):
            ?>
                <a href="edit_topic.php?topic_id=<?= $topic_id ?>"><button>Edit</button></a>
                <a href="delete_topic.php?topic_id=<?= $topic_id ?>" onclick="return confirm('Are you sure?')"><button>Delete</button></a>
            <?php endif; ?>
        </div>
    </div>
    <!-- PROFILE PART END -->

    <!-- Display topic information -->
    <p><?= $parsedown->text($topic['topic_desc']) ?></p>
</div>

<?php if (isUserOrAdmin()): ?>
    <br/><center><a href="add_comment.php?topic_id=<?= $topic_id ?>"><img src="template/images/comment.png" alt="" /></a></center>
<?php else: ?>
    <br/><center>
        If you want to comment: <a href="login.php">Login</a> or <a href="register.php">Register</a>
    </center>
<?php endif; ?>

<?php foreach ($comments as $comment): ?>
    <div style="width: 92%; border: 1px solid black; margin: 0 auto; padding:15px; padding-top: 0px; margin-top: 20px; box-shadow: 0 0 8px rgba(0, 0, 0, .8); border-radius: 5px; overflow: hidden;">
        <?php $commentAuthor = $usersModel->getUserById($comment['comment_author']); ?>
        <br>
        <!-- PROFILE PART START -->
        <div style="width: 99%; border: 1px solid black; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 0 8px rgba(0, 0, 0, .8); border-radius: 5px;">
            <div style="display: flex; align-items: center;">
                <img src="<?= htmlspecialchars($commentAuthor['avatar']) ?>" alt="" id="post-profile-image" style="margin-right: 10px; max-width: 150px; max-height: 150px; margin-left:12px;" />
                <div>
                    <div>👤 Username: <?= htmlspecialchars($commentAuthor['username']) ?></div>
                    <div><?= $commentAuthor['type'] == 2 ? '🔴 Admin' : '🔵 User' ?></div>
                    <div>✍️ Signature: <?= htmlspecialchars($commentAuthor['signature']) ?></div>
                    <div>⏳ Last active: <?= date('d M Y \a\t H:i', strtotime($commentAuthor['last_login'])) ?></div>
                    <div>📅 Commented: <?= date('d M Y \a\t H:i', strtotime($comment['date_added_comment'])) ?></div>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; padding-right: 10px;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 2;
                    $isAuthor = isset($_SESSION['user_id']) && $comment['comment_author'] == $_SESSION['user_id'];

                    if ($isAdmin || $isAuthor):
                    ?>
                        <a href="edit_comment.php?comment_id=<?= $comment['comment_id'] ?>&topic_id=<?= $topic_id ?>"><button>Edit</button></a>
                        <a href="delete_comment.php?comment_id=<?= $comment['comment_id'] ?>&topic_id=<?= $topic_id ?>" onclick="return confirm('Are you sure?')"><button>Delete</button></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- PROFILE PART END -->
        
        <p><?= $parsedown->text($comment['comment']) ?></p>
    </div>
<?php endforeach; ?>
<br>
<?php require_once('template/footer.php'); ?>
