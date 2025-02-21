<?php
require_once 'functions.php';
include 'template/header.php';
echo '<div id="content">';

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$form_submit = filter_input(INPUT_POST, 'form_submit');
$question = filter_input(INPUT_POST, 'question');

if ($form_submit == 1) {
	
	if($question != 8){ echo 'wrong question'; exit;}

    $sql = "SELECT user_id, username, password, type, avatar, signature FROM users WHERE username = :username";
    $params = [
        ":username" => $username
    ];
    $stmt = run_q($sql, $params);

    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['is_loged'] = true;
            $_SESSION['user_info'] = $row;

            $update_sql = "UPDATE users SET last_login = :last_login WHERE user_id = :user_id";
            $update_params = [
                ":last_login" => date('Y-m-d H:i:s'),
                ":user_id" => $_SESSION['user_info']['user_id']
            ];
            $update_result = run_q($update_sql, $update_params);

            if ($update_result) {
                redirect('index.php');
            } else {
                echo "Error updating last_login."; // Translated message
            }
        } else {
            echo 'Incorrect username or password.'; // Translated message
        }
    } else {
        echo "Error executing the query."; // Translated message
    }
}
?>

<center>
    <form action="login.php" method="post">
        <label for="username">Username:</label> <input type="text" id="username" name="username" placeholder="Enter username" required><br/>
        <label for="password">Password:</label> <input type="password" id="password" name="password" placeholder="Enter password" required><br/>
		
		<label for="question"><b>Question:</b> How much is <b>2</b> PLUS <br /><img src="template/images/question.png" alt="" /></label><br>
		<input type="text" id="question" name="question" size="50" required><br><br>
		
        <input type="hidden" name="form_submit" value="1">
        <input type="submit" value="Log in">  </form>
</center>

<?php
echo '</div>'; // Close id=content
include 'aside.php';
include 'template/footer.php';
?>