<?php
include '../functions.php';

if (isset($_SESSION['is_loged'])) {
    $topic_id = (int)filter_input(INPUT_GET, 'topic_id');
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');
    $comment_id = (int)filter_input(INPUT_GET, 'comment_id');

    // Check if user has rights to delete the comment (с prepared statement)
    $check_sql = "SELECT comment_author FROM comments WHERE comment_id = :comment_id"; // Използваме comment_id, за да е по-точно
    $check_params = [":comment_id" => $comment_id];
    $check_stmt = run_q($check_sql, $check_params);

    if ($check_stmt) {
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && ($_SESSION['user_info']['user_id'] == $row['comment_author'] || $_SESSION['user_info']['type'] == 2)) {
            // Изтриваме коментара (с prepared statement)
            $delete_sql = "DELETE FROM comments WHERE comment_id = :comment_id";
            $delete_params = [":comment_id" => $comment_id];
            $result = run_q($delete_sql, $delete_params);

            if ($result) {
                redirect('../topic.php?topic_id=' . $topic_id . '&cat_id=' . $cat_id);
            } else {
                echo "Грешка при изтриване на коментара."; // Обработка на грешката
            }
        } else {
            echo 'Нямате права да изтриете този коментар.'; // По-ясно съобщение за грешка
        }
    } else {
        echo "Грешка при проверка на правата за изтриване."; // Обработка на грешката
    }
} else {
    echo 'Не сте влезли в системата.'; // По-ясно съобщение за грешка
}

?>