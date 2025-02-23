<?php
// login.php
session_start();
include_once 'core/database.php';
include_once 'models/Users.php';

$database = new Database();
$db = $database->connect();

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

<h2>Вход</h2>
<form method="POST" action="login.php">
    <input type="text" name="username" placeholder="Потребителско име" required>
    <input type="password" name="password" placeholder="Парола" required>
    <button type="submit">Вход</button>
</form>
<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>