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

// Create a database connection
$conn = $database->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input data
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_NUMBER_INT);

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

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Prepare data for insertion
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $signature = "No Signature";
        $last_login = date('Y-m-d H:i:s');
        $created = date('Y-m-d H:i:s');

        // Check for existing user by username or email
        $check_sql = "SELECT 1 FROM users WHERE username = :username OR email = :email";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([
            ":username" => $username,
            ":email" => $email
        ]);

        if ($check_stmt->rowCount() > 0) {
            $errors[] = 'Username or email address already exists.';
        } else {
            // Validate the question answer
            if ($question != 8) {
                echo '<div class="error">Wrong question answer.</div>';
            } else {
                // Insert new user
                $insert_sql = "INSERT INTO users (username, email, password, signature, last_login, created) 
                               VALUES (:username, :email, :password, :signature, :last_login, :created)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_result = $insert_stmt->execute([
                    ":username"   => $username,
                    ":email"      => $email,
                    ":password"   => $hashed_password,
                    ":signature"  => $signature,
                    ":last_login" => $last_login,
                    ":created"    => $created
                ]);

                if ($insert_result) {
                    echo '<div class="success">Registration was successful!</div>';

                    // Automatic login after registration
                    $login_sql = "SELECT user_id, username, type, avatar, signature, password 
                                  FROM users WHERE username = :username";
                    $login_stmt = $conn->prepare($login_sql);
                    $login_stmt->execute([":username" => $username]);

                    if ($row = $login_stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (password_verify($password, $row['password'])) {
                            $_SESSION['is_loged'] = true;
                            $_SESSION['user_id'] = $row['user_id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['type'] = (int)$row['type'];
                            // Use default avatar if none is set
                            $_SESSION['avatar'] = $row['avatar'] ? $row['avatar'] : 'uploads/avatar-default.avif';
                            $_SESSION['signature'] = $row['signature'];

                            session_regenerate_id(true);

                            // Update last_login timestamp for the logged in user
                            $update_login_sql = "UPDATE users SET last_login = :last_login WHERE user_id = :user_id";
                            $update_login_stmt = $conn->prepare($update_login_sql);
                            $update_login_stmt->execute([
                                ":last_login" => date('Y-m-d H:i:s'),
                                ":user_id"    => $row['user_id']
                            ]);

                            header('Location: index.php');
                            exit; // Stop further script execution after redirection
                        } else {
                            echo '<div class="error">Incorrect username or password.</div>';
                        }
                    } else {
                        echo '<div class="error">Error with automatic login.</div>';
                    }
                } else {
                    $errors[] = 'Error during registration. Please try again later.';
                }
            }
        }
    }
}

// Display errors if any
if (!empty($errors)) {
    echo '<div class="errors">';
    foreach ($errors as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
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

    <label for="question">
        <b>Question:</b> How much is <b>2</b> PLUS <br /><img src="template/images/question.png" alt="" />
    </label><br>
    <input type="text" id="question" name="question" size="50" required><br><br>

    <input type="submit" value="Register">
</form>
</div>

<?php
include 'aside.php';
include 'template/footer.php';
?>
