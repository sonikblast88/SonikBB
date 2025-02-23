<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && isset($_SESSION['user_info']) && isset($_SESSION['user_info']['type']) && ($_SESSION['user_info']['type'] == 1 || $_SESSION['user_info']['type'] == 2)){
    $topic_id = (int)filter_input(INPUT_GET, 'topic_id');
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');

    // Check if user has rights to delete the topic (using prepared statement)
    $check_sql = "SELECT topic_author FROM topics WHERE topic_id = :topic_id";
    $check_params = [":topic_id" => $topic_id];
    $check_stmt = run_q($check_sql, $check_params);

    if ($check_stmt) {
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && ($_SESSION['user_info']['user_id'] == $row['topic_author'] || $_SESSION['user_info']['type'] == 2)) {
            // Delete the topic (using prepared statement)
            $delete_topic_sql = "DELETE FROM topics WHERE topic_id = :topic_id";
            $delete_topic_params = [":topic_id" => $topic_id];
            $result_topic = run_q($delete_topic_sql, $delete_topic_params);

            if ($result_topic) {
                // If deleting the topic is successful, delete the comments (using prepared statement)
                $delete_comments_sql = "DELETE FROM comments WHERE topic_id = :topic_id";
                $delete_comments_params = [":topic_id" => $topic_id];
                $result_comments = run_q($delete_comments_sql, $delete_comments_params);

                if ($result_comments) {
                    redirect('../topics.php?cat_id=' . $cat_id);
                } else {
                    echo "Error deleting comments for this topic."; // Error handling
                }
            } else {
                echo "Error deleting the topic."; // Error handling
            }
        } else {
            echo 'You do not have permission to delete this topic.'; // Clearer error message
        }
    } else {
        echo "Error checking delete permissions."; // Error handling
    }
} else {
    echo 'You are not logged in.'; // Clearer error message
}

?>