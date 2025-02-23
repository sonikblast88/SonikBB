<?php
// login.php
session_start();
include_once 'core/autoload.php';

include_once 'core/autoload.php';
include_once 'models/Category.php';
include_once 'models/Users.php';
include_once 'models/Topics.php';

include 'template/header.php';

$database = new Database();
$db = $database->connect();

$categoryModel = new Category($db);
$topicsModel = new Topics($db);
$usersModel = new Users($db);

// Обработка на формата за вход
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Извличане на потребителя по потребителско име
    $user = $usersModel->getUserByUsername($username);

    // Проверка на паролата
    if ($user && hash_equals($user['password'], crypt($password, $user['password']))) {
        // Създаване на сесия
        $_SESSION['is_loged'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['type'] = (int)$user['type']; // 1 - user, 2 - admin
		$_SESSION['avatar'] = $user['avatar'];
		$_SESSION['signature'] = $user['signature'];

        session_regenerate_id(true); // Регенерация на идентификатора на сесията

        header("Location: index.php");
        exit;
    } else {
        $error = "Невалидно потребителско име или парола.";
    }
}
?>
<div id="content">

	<h2>Вход</h2>
	<form method="POST" action="login.php">
		<input type="text" name="username" placeholder="Потребителско име" required>
		<input type="password" name="password" placeholder="Парола" required>
		<button type="submit">Вход</button>
	</form>
	<?php if (isset($error)): ?>
		<p style="color: red;"><?= htmlspecialchars($error) ?></p>
	<?php endif; ?>

</div>

<div id="aside">
    <div id="profile">
        <?php
        $type = '<div id="profile-info"><b>Type:</b> USER</div>';
        $stats = '';

        if (isset($_SESSION['is_loged'])) {
            if ($_SESSION['type'] == 2) {
                $type = '<div id="profile-info"><b>Type:</b> Administrator</div>';
                $stats = '<div id="profile-info"><b>» <a href="/stats.php">Forums Stats</a></b></div>';
            }

            echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
            echo '<img src="/' . htmlspecialchars($_SESSION['avatar']) . '" alt="" id="profile-image" />';
            echo '<div id="profile-info"><b>Name:</b> ' . htmlspecialchars($_SESSION['username']) . '</div>';
            echo $type;
            echo $stats;
            echo '<div id="profile-info"><b>» <a href="profile.php?profile_id=' . (int)$_SESSION['user_id'] . '">Edit Profile</a></b></div>';
            echo '<div id="profile-info"><b>» <a href="logout.php">Log Out</a></b></div>';
            echo '<div id="profile-info">' . htmlspecialchars($_SESSION['signature']) . '</div>';
        } else {
            echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
            echo '<img src="template/images/avatar-default.avif" alt="" id="profile-image" />';
            echo '<div id="profile-info">You are not currently logged in. Please <b><a href="login.php">log in</a></b> or <a href="register.php"><b>register</b></a></div>';
        }
        ?>
    </div>

    <br />
	<div id="last-topics">
		<div id="last-topics-topic-header">» L A S T - T O P I C S</div>
		<?php
		$lastTopics = $topicsModel->getLastTopics();
		while ($row = $lastTopics->fetch(PDO::FETCH_ASSOC)): ?>
			<div id="last-topics-topic">
				» <a href="topic.php?topic_id=<?= $row['topic_id'] ?>"><?= $row['topic_name'] ?></a> (<?= $row['category_name'] ?>)
			</div>
		<?php endwhile; ?>
	</div>
</div>

<?php include_once 'template/footer.php'; ?>