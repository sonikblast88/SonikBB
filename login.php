<?php
require_once 'functions.php';
include 'template/header.php';
echo '<div id="content">';

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$form_submit = filter_input(INPUT_POST, 'form_submit');

if ($form_submit == 1) {
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
                echo "Грешка при актуализиране на last_login.";
            }
        } else {
            echo 'Грешно потребителско име или парола.';
        }
    } else {
        echo "Грешка при изпълнение на заявката.";
    }
}
?>

<center>
    <form action="login.php" method="post">
        <label for="username">Потребител:</label> <input type="text" id="username" name="username" placeholder="Въведете потребителско име" required><br/>
        <label for="password">Парола:</label> <input type="password" id="password" name="password" placeholder="Въведете парола" required><br/>
        <input type="hidden" name="form_submit" value="1">
        <input type="submit" value="Вписване в системата">
    </form>
</center>

<?php
echo '</div>'; // Затварям id=content
include 'aside.php';
include 'template/footer.php';
?>