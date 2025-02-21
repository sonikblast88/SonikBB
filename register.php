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
	$question = filter_input(INPUT_POST, 'question');

    // Input data validation
	
	if($question != 8){ echo 'wrong question'; exit;}
	
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
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Password hashing
        $signature = "No Signature";
        $last_login = date('Y-m-d H:i:s');

        // Check if username or email already exist
        $check_sql = "SELECT 1 FROM users WHERE username = :username OR email = :email";
        $check_params = [":username" => $username, ":email" => $email];
        $check_result = run_q($check_sql, $check_params);

        if ($check_result && $check_result->rowCount() > 0) {
            $errors[] = 'Username or email address already exists.';
        } else {
            // User registration
            $insert_sql = "INSERT INTO users (username, email, password, signature, last_login) VALUES (:username, :email, :password, :signature, :last_login)";
            $insert_params = [
                ":username" => $username,
                ":email" => $email,
                ":password" => $hashed_password, // Use the hashed password
                ":signature" => $signature,
                ":last_login" => $last_login
            ];
            $insert_result = run_q($insert_sql, $insert_params);

            if ($insert_result) {
                echo '<div class="success">Registration was successful!</div>';

                // Automatic user login
                $login_sql = "SELECT user_id, username, type, avatar, signature, password FROM users WHERE username = :username";
                $login_params = [":username" => $username];
                $login_stmt = run_q($login_sql, $login_params);

                if ($login_stmt && $row = $login_stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (password_verify($password, $row['password'])) { // Password check
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
                        echo 'Incorrect username or password.'; // This should not happen, but it's good to have a check
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

// Display errors, if any
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

<?php
echo '</div>';
include 'aside.php';
include 'template/footer.php';
?>