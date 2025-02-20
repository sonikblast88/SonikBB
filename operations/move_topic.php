<?php
include '../functions.php';

if (isset($_POST['topic_id'], $_POST['new_cat_id']) && isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $topic_id = (int)$_POST['topic_id'];
    $new_cat_id = (int)$_POST['new_cat_id'];

    // Check for permissions (administrator or topic author)
    $check_sql = "SELECT topic_author FROM topics WHERE topic_id = :topic_id";
    $check_params = [":topic_id" => $topic_id];
    $check_stmt = run_q($check_sql, $check_params);
    $check_row = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SESSION['user_info']['type'] == 2 || ($check_row && $check_row['topic_author'] == $_SESSION['user_info']['user_id'])) {
        // Update the record in the database
        $update_sql = "UPDATE topics SET parent = :new_cat_id WHERE topic_id = :topic_id";
        $update_params = [":new_cat_id" => $new_cat_id, ":topic_id" => $topic_id];
        $update_stmt = run_q($update_sql, $update_params);

        if ($update_stmt) {
            header('Location: ' . $_SERVER['HTTP_REFERER']); // Or another appropriate page
            exit;
        } else {
            echo "Error moving the topic."; // Translated error message
        }
    } else {
        echo "You do not have permission to move this topic."; // Translated permission message
    }
} else {
    echo "Invalid parameters."; // Translated invalid parameters message
}

?>