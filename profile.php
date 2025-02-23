<?php
session_start();
include_once 'core/autoload.php';
include_once 'models/Users.php';

$database = new Database();
$db = $database->connect();
$usersModel = new Users($db);

$get_profile_id = filter_input(INPUT_GET, 'profile_id', FILTER_SANITIZE_NUMBER_INT);

// Проверка дали потребителят е логнат и дали редактира собствения си профил
if (!isset($_SESSION['is_loged']) || $_SESSION['user_id'] !== $get_profile_id) {
    header("Location: index.php");
    exit;
}

$user = $usersModel->getUserById($get_profile_id);
if (!$user) {
    die("Потребителят не е намерен.");
}

$successMessage = ""; // Инициализация на променливата за съобщение за успех

// Обработка на формата за смяна на парола
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submit'])) {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $match_password = trim($_POST['match_password']);

    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $match_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $usersModel->updatePassword($get_profile_id, $hashed_password);
            $successMessage = "Паролата беше сменена успешно. Моля, излезте и влезте отново, за да се обновят промените.";
        } else {
            $error = "Новите пароли не съвпадат.";
        }
    } else {
        $error = "Грешна стара парола.";
    }
}

// Обработка на формата за подпис
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_signature'])) {
    $signature = trim($_POST['signature']);
    $usersModel->updateSignature($get_profile_id, $signature);
    $successMessage = "Подписът беше сменен успешно.";
}

// Обработка на формата за качване на аватар
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imageUpload'])) {
    $result = $usersModel->uploadAvatar($get_profile_id, $_FILES['imageUpload']);

    if ($result === true) {
        $successMessage = "Аватарът беше сменен успешно. Моля, излезте и влезте отново, за да се обновят промените.";
    } else {
        $error = $result;
        echo $error;
    }
}

include 'template/header.php';
?>
<div id="content">
    <h2>Редактиране на профил</h2>
    <p>Здравей, <b><?= htmlspecialchars($user['username']) ?></b>. Тук можеш да редактираш профила си.</p>

    <?php if (!empty($successMessage)): ?>
        <p style="color: green;"><?= $successMessage ?></p>
    <?php endif; ?>

    <h3>Смяна на парола</h3>
    <form method="post" action="profile.php?profile_id=<?= $get_profile_id ?>">
        <input type="password" name="old_password" placeholder="Стара парола" required><br>
        <input type="password" name="new_password" placeholder="Нова парола" required><br>
        <input type="password" name="match_password" placeholder="Повторете новата парола" required><br>
        <input type="hidden" name="form_submit" value="1">
        <button type="submit">Смени паролата</button>
    </form>

    <h3>Редактиране на подпис</h3>
    <form method="post" action="profile.php?profile_id=<?= $get_profile_id ?>">
        <textarea name="signature" rows="4" cols="50"><?= htmlspecialchars($user['signature']) ?></textarea><br>
        <input type="hidden" name="submit_signature" value="1">
        <button type="submit">Запази подписа</button>
    </form>

    <h3>Смяна на аватар</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="imageUpload" accept="image/*" required><br>
        <button type="submit">Качи аватар</button>
    </form>
</div>
<?php include 'template/footer.php'; ?>