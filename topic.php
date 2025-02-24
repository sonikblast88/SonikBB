<?php
// topic.php
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

// Проверка за администратор
$is_admin = isAdmin();

// Проверка за администратор или потребител
$isUserOrAdmin = isUserOrAdmin();

// Вземане на ID на темата от URL
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Извличане на информация за темата
$topic = $topicsModel->getTopicById($topic_id);

// Извличане на информация за потребителя, който е създал темата
$user = $usersModel->getUserById($topic['topic_author']);

// Извличане на коментарите за темата
$comments = $commentsModel->getCommentsByTopicId($topic_id);

// Обработка на формата за добавяне на коментар
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_comment'])) {
        $comment = $_POST['comment'];
        $comment_author = $_SESSION['user_id']; // Текущият потребител

        if ($commentsModel->addComment($topic_id, $comment, $comment_author)) {
            header("Location: topic.php?topic_id=" . $topic_id);
            exit();
        }
    } elseif (isset($_POST['edit_comment'])) {
        // Редактиране на коментар
        $comment_id = $_POST['comment_id'];
        $comment = $_POST['comment'];

        // Проверка дали потребителят има права да редактира коментара
        $commentData = $commentsModel->getCommentById($comment_id);
        if ($_SESSION['type'] == 2 || $commentData['comment_author'] == $_SESSION['user_id']) {
            if ($commentsModel->updateComment($comment_id, $comment)) {
                header("Location: topic.php?topic_id=" . $topic_id);
                exit();
            }
        }
    } elseif (isset($_POST['delete_comment'])) {
        // Изтриване на коментар
        $comment_id = $_POST['comment_id'];

        // Проверка дали потребителят има права да изтрие коментара
        $commentData = $commentsModel->getCommentById($comment_id);
        if ($_SESSION['type'] == 2 || $commentData['comment_author'] == $_SESSION['user_id']) {
            if ($commentsModel->deleteComment($comment_id)) {
                header("Location: topic.php?topic_id=" . $topic_id);
                exit();
            }
        }
    }
}


require_once('template/header.php');
?>
<div style="width: 92%; border: 1px solid black; margin: 0 auto;padding:15px;padding-top: 0px;margin-top: 20px;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;overflow: hidden;">
	<h2><?= $topic['topic_name'] ?></h2>

	<!-- PROFILE PART START -->
	<div style="width: 99%; border: 1px solid black; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;">
		<div style="display: flex; align-items: center;">
		<img src="<?= $user['avatar'] ?>" alt="" id="post-profile-image" style="margin-right: 10px; max-width: 150px; max-height: 150px;" />
		<div>
		<div>Username: <?= $user['username'] ?></div>
		<div>Signature: <?= $user['signature'] ?></div>
		</div>
	</div>
	<div style="display: flex; flex-direction: column; padding-right: 10px;">
			<?php
			$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 2;
			$isTopicAuthor = false; // Инициализация
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

<!-- Показване на информация за темата -->

<p><?= $parsedown->text($topic['topic_desc']) ?></p>
</div>

<?php if(isUserOrAdmin()): ?>
<br/><center><a href = "add_comment.php?topic_id=<?= $topic_id ?>"><img src = "template/images/comment.png" alt = "" /></a></center>
<?php endif; ?>

    <?php foreach ($comments as $comment): ?>
		<div style="width: 92%; border: 1px solid black; margin: 0 auto;padding:15px;padding-top: 0px;margin-top: 20px;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;overflow: hidden;">
            <?php $commentAuthor = $usersModel->getUserById($comment['comment_author']); ?>
			<br>
			<!-- PROFILE PART START -->
			<div style="width: 99%; border: 1px solid black; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;">
			<div style="display: flex; align-items: center;">
			<img src="<?= $commentAuthor['avatar'] ?>" alt="" id="post-profile-image" style="margin-right: 10px; max-width: 150px; max-height: 150px;" />
			<div>
			<div>Username: <?= $commentAuthor['username'] ?></div>
			<div>Signature: <?= $user['signature'] ?></div>
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
			
            <p><?= $parsedown->text($comment['comment']); ?></p>
        </div>
    <?php endforeach; ?>
<br>
<?php require_once('template/footer.php'); ?>
