<?php
session_start();
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

$error = ''; // Дефиниране на празна грешка

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_NUMBER_INT);

    if ((int)$question !== 8) {
        $error = "Невалиден код на въпрос.";
    } else {
        $user = $usersModel->getUserByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['is_loged'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['type'] = (int)$user['type'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['signature'] = $user['signature'];

			// Ъпдейт на last_login
			$usersModel->updateLastLogin($user['user_id']);

            session_regenerate_id(true);
            header("Location: index.php");
            exit;
        } else {
            $error = "Невалидно потребителско име или парола.";
        }
    }
}
?>

<div id="content">
    <h2>Вход</h2>
    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Потребителско име" required>
        <input type="password" name="password" placeholder="Парола" required>

        <label for="question"><b>Question:</b> How much is <b>2</b> PLUS <br /><img src="template/images/question.png" alt="" /></label><br>
        <input type="text" id="question" name="question" size="50" required><br><br>

        <button type="submit">Вход</button>
    </form>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>

<?php 
include_once 'aside.php';
include_once 'template/footer.php';
?>
