<?php

if (filter_input(INPUT_GET, 'topic_id') > 0) {
    $sql = "SELECT * FROM comments, users WHERE topic_id = :topic_id AND user_id = comment_author ORDER BY comment_id DESC";
    $params = [":topic_id" => $topic_id];
    $stmt = run_q($sql, $params);

    if ($stmt) { // Check for successful query result
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $del = '';
            $user_type = '';
            $ed = '';

            if ($row['type'] == 1) {
                $user_type = 'User';
            } elseif ($row['type'] == 2) {
                $user_type = 'Administrator';
            }

            if (isset($_SESSION['is_loged']) && ($_SESSION['user_info']['user_id'] == $row['comment_author'] || $_SESSION['user_info']['type'] == 2)) {
                $del = '<div id = "profile-info">[ <a href="operations/del_comment.php?comment_id=' . $row['comment_id'] . '&cat_id=' . $cat_id . '&topic_id=' . $topic_id . '" onclick="return confirm(\'Are you sure you want to delete this comment?\')">Delete Comment</a> ]</div>';
                $ed = '<div id = "profile-info">[ <a href="operations/edit_comment.php?comment_id=' . $row['comment_id'] . '&cat_id=' . $cat_id . '&topic_id=' . $topic_id . '">Edit Comment</a> ]</div>';
            }

            $markdownText = $row['comment'];
            $htmlText = $Parsedown->text($markdownText);

            echo '<div style="width: 92%; border: 1px solid black; margin: 0 auto;padding:15px;padding-top: 0px;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;">';
            // PROFILE PART START
            echo '<div style="width: 99%; border: 1px solid black; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px; margin-top:20px; margin-bottom: 20px;">';
            echo '<div style="display: flex; align-items: center;">';
            echo '<img src="' . $path . $row['avatar'] . '" alt="" id="post-profile-image" style="margin-right: 10px; max-width: 150px; max-height: 150px;" />'; // Limit avatar size
            echo '<div>';
            echo '<div>Username: ' . htmlspecialchars($row['username'], ENT_QUOTES) . '</div>'; // Escape username
            echo '<div>Type: ' . $user_type . '</div>';
            echo '<div>Signature: ' . htmlspecialchars($row['signature'], ENT_QUOTES) . '</div>'; // Escape signature
            echo '</div>';
            echo '</div>';
            echo '<div style="display: flex; flex-direction: column; padding-right: 10px;">';
            echo $del;
            echo $ed;
            echo '</div>';
            echo '</div>';
            // PROFILE PART END

            echo $htmlText; // No need for htmlspecialchars, Parsedown already does it
            echo '</div>';

            if (isset($_SESSION['is_loged'])) {
                echo '<br/><center><a href = "operations/add_comment.php?topic_id=' . $topic_id . '&cat_id=' . $cat_id . '"><img src = "template/images/comment.png" alt = "" /></a><br /><br></center>';
            } else {
                echo '<center> --------------------------------- </center>';
            }
        }
    } else {
        echo "Error executing the query."; // Error handling
    }
} else {
    redirect('index.php');
}

?>