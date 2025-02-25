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

echo '<div id="content">';

$errors = [];

// Създаване на връзка с базата данни
$conn = $database->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Валидиране на входните данни
    $username = filter_input(INPUT_POST, 'username');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $question = filter_input(INPUT_POST, 'question');

    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 42) {
        $errors[] = 'Username must be between 3 and 42 characters.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif ($email === false) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password != $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Подготовка на данни за вмъкване
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $signature = "No Signature";
        $last_login = date('Y-m-d H:i:s');
        $created = date('Y-m-d H:i:s');

        // Проверка за съществуващ потребител
        $check_sql = "SELECT 1 FROM users WHERE username = :username OR email = :email";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([":username" => $username, ":email" => $email]);

        if ($check_stmt->rowCount() > 0) {
            $errors[] = 'Username or email address already exists.';
        } else {
            if ($question != 8) {
                echo 'wrong question';
            } else {
                // Вмъкване на нов потребител
                $insert_sql = "INSERT INTO users (username, email, password, signature, last_login, created) VALUES (:username, :email, :password, :signature, :last_login, :created)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_result = $insert_stmt->execute([
                    ":username" => $username,
                    ":email" => $email,
                    ":password" => $hashed_password,
                    ":signature" => $signature,
                    ":last_login" => $last_login,
                    ":created" => $created
                ]);

                if ($insert_result) {
                    echo '<div class="success">Registration was successful!</div>';

                    // Автоматично влизане
                    $login_sql = "SELECT user_id, username, type, avatar, signature, password FROM users WHERE username = :username";
                    $login_stmt = $conn->prepare($login_sql);
                    $login_stmt->execute([":username" => $username]);

                    if ($row = $login_stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (password_verify($password, $row['password'])) {
                            $_SESSION['is_loged'] = true;
                            $_SESSION['user_id'] = $row['user_id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['type'] = (int)'1';
                            $_SESSION['avatar'] = 'uploads/avatar-default.avif';
                            $_SESSION['signature'] = $user['signature'];

                            session_regenerate_id(true);

                            $update_login_sql = "UPDATE users SET last_login = :last_login WHERE user_id = :user_id";
                            $update_login_stmt = $conn->prepare($update_login_sql);
                            $update_login_stmt->execute([
                                ":last_login" => date('Y-m-d H:i:s'),
                                ":user_id" => $_SESSION['user_info']['user_id']
                            ]);

                            header('Location: index.php');
                            exit; // Добавяме exit, за да спре изпълнението на скрипта след пренасочване
                        } else {
                            echo 'Incorrect username or password.';
                        }
                    } else {
                        echo "Error with automatic login.";
                    }
                } else {
                    $errors[] = 'Error during registration. Please try again later.';
                }
            }
        }
    }
}

// Показване на грешки
if (!empty($errors)) {
    echo '<div class="errors">';
    foreach ($errors as $error) {
        echo '<p>' . $error . '</p>';
    }
    echo '</div>';
}
?>

<form action="register.php" method="post">
    <label for="username">Username:</label><br>
    <input type="text" name="username" id="username" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" name="email" id="email" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" name="password" id="password" required><br><br>

    <label for="confirm_password">Confirm Password:</label><br>
    <input type="password" name="confirm_password" id="confirm_password" required><br><br>

    <label for="question"><b>Question:</b> How much is <b>2</b> PLUS <br /><img src="template/images/question.png" alt="" /></label><br>
    <input type="text" id="question" name="question" size="50" required><br><br>

    <input type="submit" value="Register">
</form>
</div>

<?php
include 'aside.php';
include 'template/footer.php';
?>
