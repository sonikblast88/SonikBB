<?php
require_once 'functions.php';
include 'template/header.php';
echo '<div id="content">';

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$confirm_password = filter_input(INPUT_POST, 'confirm_password');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$form_submit = filter_input(INPUT_POST, 'form_submit');

if ($form_submit == 1) {
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        echo 'Моля, попълнете всички полета.';
    } elseif ($password !== $confirm_password) {
        echo 'Паролите не съвпадат.';
    } elseif (strlen($username) < 3) {
        echo 'Потребителското име трябва да е поне 3 символа.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Невалиден формат на имейла.';
    } else {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USERNAME, DB_PASSWORD); // Създаваме PDO връзка директно, защото run_q() връща PDOStatement само за SELECT
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->beginTransaction();

            // Проверка дали потребителското име вече съществува (използваме run_q())
            $stmt_username = run_q("SELECT user_id FROM users WHERE username = :username", [':username' => $username]);
            if ($stmt_username && $stmt_username->rowCount() > 0) { // Проверяваме $stmt_username за да сме сигурни че заявката е успешна
                throw new Exception("Потребителското име вече съществува.");
            }

            // Проверка дали имейлът вече съществува (използваме run_q())
            $stmt_email = run_q("SELECT user_id FROM users WHERE email = :email", [':email' => $email]);
            if ($stmt_email && $stmt_email->rowCount() > 0) { // Проверяваме $stmt_email за да сме сигурни че заявката е успешна
                throw new Exception("Този имейл вече е регистриран.");
            }

            // Добавяне на новия потребител в базата данни (използваме run_q())
            $sql = "INSERT INTO users (signature, username, password, type, avatar, last_login, email) 
                    VALUES ('No Signature', :username, :password, '1', 'template/images/avatar-default.jpg', NOW(), :email)";
            $stmt_insert = $conn->prepare($sql); // Подготвяме INSERT заявката
            $stmt_insert->execute([':username' => $username, ':password' => $password, ':email' => $email]); // Изпълняваме я

            if ($stmt_insert->errorCode() != '00000') { // Проверяваме за грешки след изпълнението на заявката
                $errorInfo = $stmt_insert->errorInfo();
                throw new Exception("Грешка при добавяне на потребител: " . $errorInfo[2]);
            }

            $conn->commit();

            echo 'Регистрацията е успешна! Можете да <a href="login.php">влезете</a> в системата.'; // Извеждаме съобщение за успех

        } catch (PDOException $e) { // Хващаме PDOException, а не Exception
            if ($conn) { // Проверяваме дали $conn е инициализиран, преди да извикаме rollBack()
                $conn->rollBack();
            }
            error_log($e->getMessage());
            echo $e->getMessage();
        }
    }
}
?>

<center>
    <h2>Регистрация</h2>
    <form action="register.php" method="post">
        <label for="username">Потребителско име:</label>
        <input type="text" id="username" name="username" placeholder="Въведете потребителско име" required minlength="3" autocomplete="username"><br/>

        <label for="email">Имейл:</label>
        <input type="email" id="email" name="email" placeholder="Въведете вашия имейл" required autocomplete="email"><br/>

        <label for="password">Парола:</label>
        <input type="password" id="password" name="password" placeholder="Въведете парола" required minlength="6" autocomplete="new-password"><br/>

        <label for="confirm_password">Потвърдете паролата:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Потвърдете паролата" required minlength="6" autocomplete="new-password"><br/>

        <input type="hidden" name="form_submit" value="1">
        <input type="submit" value="Регистрирай се">
    </form>
</center>

<?php
echo '</div>';
include 'aside.php';
include 'template/footer.php';
?>