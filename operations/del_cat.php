<?php
include '../functions.php';

if (isset($_SESSION['is_logged']) && $_SESSION['user_info']['type'] == 2) {
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');

    // Using prepared statement to delete categories
    $sql_cat = "DELETE FROM categories WHERE cat_id = :cat_id";
    $params_cat = [":cat_id" => $cat_id];
    $result_cat = run_q($sql_cat, $params_cat);

    if ($result_cat) {
        // If deleting the category is successful, continue with deleting the topics
        $sql_topics = "DELETE FROM topics WHERE parent = :cat_id";
        $params_topics = [":cat_id" => $cat_id];
        $result_topics = run_q($sql_topics, $params_topics);

        if ($result_topics) {
            redirect('../index.php');
        } else {
            echo "Error deleting topics."; // Error handling
        }
    } else {
            echo "Error deleting category."; // Error handling
    }
} else {
    redirect('../index.php'); // Redirect users who do not have rights
    exit;
}

?>