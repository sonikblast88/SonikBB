<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && isset($_SESSION['user_info']) && isset($_SESSION['user_info']['type']) && ($_SESSION['user_info']['type'] == 1 || $_SESSION['user_info']['type'] == 2)){
    $topic_id = (int)filter_input(INPUT_GET, 'topic_id');
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');
    $comment_id = (int)filter_input(INPUT_GET, 'comment_id');

    // Check if user has rights to delete the comment (using prepared statement)
    $check_sql = "SELECT comment_author FROM comments WHERE comment_id = :comment_id"; // Using comment_id for accuracy
    $check_params = [":comment_id" => $comment_id];
    $check_stmt = run_q($check_sql, $check_params);

    if ($check_stmt) {
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && ($_SESSION['user_info']['user_id'] == $row['comment_author'] || $_SESSION['user_info']['type'] == 2)) {
            // Delete the comment (using prepared statement)
            $delete_sql = "DELETE FROM comments WHERE comment_id = :comment_id";
            $delete_params = [":comment_id" => $comment_id];
            $result = run_q($delete_sql, $delete_params);

            if ($result) {
                redirect('../topic.php?topic_id=' . $topic_id . '&cat_id=' . $cat_id);
            } else {
                echo "Error deleting comment."; // Error handling
            }
        } else {
            echo 'You do not have permission to delete this comment.'; // Clearer error message
        }
    } else {
        echo "Error checking delete permissions."; // Error handling
    }
} else {
    echo 'You are not logged in.'; // Clearer error message
}

?>