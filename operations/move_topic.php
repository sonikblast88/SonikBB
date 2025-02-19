<?php
include '../functions.php';

if (isset($_POST['topic_id'], $_POST['new_cat_id']) && isset($_SESSION['is_loged'])) {
    $topic_id = (int)$_POST['topic_id'];
    $new_cat_id = (int)$_POST['new_cat_id'];

    // Проверка за права (администратор или автор на темата)
    $check_sql = "SELECT topic_author FROM topics WHERE topic_id = :topic_id";
    $check_params = [":topic_id" => $topic_id];
    $check_stmt = run_q($check_sql, $check_params);
    $check_row = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SESSION['user_info']['type'] == 2 || ($check_row && $check_row['topic_author'] == $_SESSION['user_info']['user_id'])) {
        // Обновяване на записа в базата данни
        $update_sql = "UPDATE topics SET parent = :new_cat_id WHERE topic_id = :topic_id";
        $update_params = [":new_cat_id" => $new_cat_id, ":topic_id" => $topic_id];
        $update_stmt = run_q($update_sql, $update_params);

        if ($update_stmt) {
            header('Location: ' . $_SERVER['HTTP_REFERER']); // Или друга подходяща страница
            exit;
        } else {
            echo "Грешка при преместване на темата.";
        }
    } else {
        echo "Нямате права да преместите тази тема.";
    }
} else {
    echo "Невалидни параметри.";
}

?>