<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');

    // Използваме prepared statement за изтриване на категории
    $sql_cat = "DELETE FROM categories WHERE cat_id = :cat_id";
    $params_cat = [":cat_id" => $cat_id];
    $result_cat = run_q($sql_cat, $params_cat);

    if ($result_cat) {
        // Ако изтриването на категорията е успешно, продължаваме с изтриването на темите
        $sql_topics = "DELETE FROM topics WHERE parent = :cat_id";
        $params_topics = [":cat_id" => $cat_id];
        $result_topics = run_q($sql_topics, $params_topics);

        if ($result_topics) {
            redirect('../index.php');
        } else {
            echo "Грешка при изтриване на теми."; // Обработка на грешката
        }
    } else {
        echo "Грешка при изтриване на категория."; // Обработка на грешката
    }
} else {
    redirect('../index.php'); // Пренасочваме потребители, които нямат права
    exit;
}

?>