<?php
require_once 'functions.php';
include 'template/header.php';
echo '<div id="content">';

// Инициализиране на масива с грешки
$errors = [];

// Проверка дали формата е изпратена
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Валидация на входните данни
    if (empty($_POST['username'])) {
        $errors[] = 'Потребителското име е задължително.';
    } elseif (strlen($_POST['username']) < 3 || strlen($_POST['username']) > 42) {
        $errors[] = 'Потребителското име трябва да е между 3 и 42 символа.';
    }

    if (empty($_POST['email'])) {
        $errors[] = 'Имейлът е задължителен.';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Невалиден формат на имейл адреса.';
    }

    if (empty($_POST['password'])) {
        $errors[] = 'Паролата е задължителна.';
    } elseif (strlen($_POST['password']) < 6) {
        $errors[] = 'Паролата трябва да е минимум 6 символа.';
    }

    if ($_POST['password'] != $_POST['confirm_password']) {
        $errors[] = 'Паролите не съвпадат.';
    }

    // Ако няма грешки, регистрираме потребителя

	if (empty($errors)) {
		$username = $_POST['username'];
		$email = $_POST['email'];
		//$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		$password = $_POST['password']; // Използваме директно паролата, без хеширане
		$signature = ""; // Default signature

		// The fix: Provide a value for 'last_login'
		$last_login = date('Y-m-d H:i:s'); // Current timestamp


		$check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
		$check_params = [$username, $email];
		$check_result = run_q($check_sql, $check_params);

		$check_rows = $check_result ? $check_result->fetchAll() : [];

		if (count($check_rows) > 0) {
			$errors[] = 'Потребителско име или имейл адрес вече съществуват.';
		} else {
			$insert_sql = "INSERT INTO users (username, email, password, signature, last_login) VALUES (?, ?, ?, ?, ?)"; // Include last_login in the query
			$insert_params = [$username, $email, $password, $signature, $last_login]; // Include last_login in the parameters
			$insert_result = run_q($insert_sql, $insert_params);

			if ($insert_result) {
				echo '<div class="success">Регистрацията беше успешна!</div>';
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
