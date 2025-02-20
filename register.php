<?php
require_once 'functions.php';
include 'template/header.php';
echo '<div id="content">';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Валидация на входните данни
    if (empty($username)) {
        $errors[] = 'Потребителското име е задължително.';
    } elseif (strlen($username) < 3 || strlen($username) > 42) {
        $errors[] = 'Потребителското име трябва да е между 3 и 42 символа.';
    }

    if (empty($email)) {
        $errors[] = 'Имейлът е задължителен.';
    } elseif ($email === false) {
        $errors[] = 'Невалиден формат на имейл адреса.';
    }

    if (empty($password)) {
        $errors[] = 'Паролата е задължителна.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Паролата трябва да е минимум 6 символа.';
    }

    if ($password != $confirm_password) {
        $errors[] = 'Паролите не съвпадат.';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Хеширане на паролата
        $signature = "No Signature";
        $last_login = date('Y-m-d H:i:s');

        // Проверка дали потребителското име или email вече съществуват
        $check_sql = "SELECT 1 FROM users WHERE username = :username OR email = :email";
        $check_params = [":username" => $username, ":email" => $email];
        $check_result = run_q($check_sql, $check_params);

        if ($check_result && $check_result->rowCount() > 0) {
            $errors[] = 'Потребителско име или имейл адрес вече съществуват.';
        } else {
            // Регистрация на потребителя
            $insert_sql = "INSERT INTO users (username, email, password, signature, last_login) VALUES (:username, :email, :password, :signature, :last_login)";
            $insert_params = [
                ":username" => $username,
                ":email" => $email,
                ":password" => $hashed_password, // Използваме хешираната парола
                ":signature" => $signature,
                ":last_login" => $last_login
            ];
            $insert_result = run_q($insert_sql, $insert_params);

            if ($insert_result) {
                echo '<div class="success">Регистрацията беше успешна!</div>';

                // Автоматично влизане на потребителя
                $login_sql = "SELECT user_id, username, type, avatar, signature, password FROM users WHERE username = :username";
                $login_params = [":username" => $username];
                $login_stmt = run_q($login_sql, $login_params);

                if ($login_stmt && $row = $login_stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (password_verify($password, $row['password'])) { // Проверка на паролата
                        $_SESSION['is_loged'] = true;
                        $_SESSION['user_info'] = $row;

                        $update_login_sql = "UPDATE users SET last_login = :last_login WHERE user_id = :user_id";
                        $update_login_params = [
                            ":last_login" => date('Y-m-d H:i:s'),
                            ":user_id" => $_SESSION['user_info']['user_id']
                        ];
                        run_q($update_login_sql, $update_login_params);

                        redirect('index.php');
                    } else {
                        echo 'Грешно потребителско име или парола.'; // Това не би трябвало да се случи, но е добре да има проверка
                    }
                } else {
                    echo "Грешка при автоматичното влизане.";
                }

            } else {
                $errors[] = 'Грешка при регистрацията. Моля, опитайте по-късно.';
            }
        }
    }
}

// Извеждаме грешките, ако има такива
if (!empty($errors)) {
    echo '<div class="errors">';
    foreach ($errors as $error) {
        echo '<p>' . $error . '</p>';
    }
    echo '</div>';
}
?>

<form action="register.php" method="post">
    <label for="username">Потребителско име:</label><br>
    <input type="text" name="username" id="username" required><br><br>

    <label for="email">Имейл:</label><br>
    <input type="email" name="email" id="email" required><br><br>

    <label for="password">Парола:</label><br>
    <input type="password" name="password" id="password" required><br><br>

    <label for="confirm_password">Потвърждение на паролата:</label><br>
    <input type="password" name="confirm_password" id="confirm_password" required><br><br>

    <input type="submit" value="Регистрация">
</form>

<?php
echo '</div>';
include 'aside.php';
include 'template/footer.php';
?>