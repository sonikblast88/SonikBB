<?php

include 'functions.php';
include 'template/header.php';
echo '<div id="content">';
echo '<div id = "topic">';

$cat_id = (int) filter_input(INPUT_GET, 'cat_id');

if ($cat_id > 0) {
    $sql = "SELECT t.topic_id, t.topic_name, t.topic_author, u.user_id, u.username 
            FROM topics AS t
            INNER JOIN users AS u ON t.topic_author = u.user_id
            WHERE t.parent = :cat_id
            ORDER BY t.topic_id DESC";
    $params = [":cat_id" => $cat_id];
    $stmt = run_q($sql, $params);

    if ($stmt) {
        $num_results = $stmt->rowCount();
        if ($num_results > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment_count_sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE topic_id = :topic_id";
                $comment_count_params = [":topic_id" => $row['topic_id']];
                $comment_count_stmt = run_q($comment_count_sql, $comment_count_params);
                $comment_count_row = $comment_count_stmt->fetch(PDO::FETCH_ASSOC);
                $comment_count = $comment_count_row['comment_count'];

                $del = '';
                if (isset($_SESSION['is_loged']) && ($_SESSION['user_info']['type'] == 2 || (isset($_SESSION['is_loged']) && $row['topic_author'] == $_SESSION['user_info']['user_id']))) {
                    $del = '[ <a href="operations/del_topic.php?topic_id=' . $row['topic_id'] . '&cat_id=' . $cat_id . '" onclick="return confirm(\'Are you sure you want to delete this topic?\')">X</a> ]';

                    // Move Topic Form
                    $move_form = '<br><form action="operations/move_topic.php" method="post" style="display: inline;">'; // Inline form
                    $move_form .= '<input type="hidden" name="topic_id" value="' . $row['topic_id'] . '">';
                    $move_form .= '<input type="hidden" name="current_cat_id" value="' . $cat_id . '">'; // Add current cat_id
                    $move_form .= '<select name="new_cat_id" id="new_cat_id">';

                    $categories_sql = "SELECT cat_id, cat_name FROM categories";
                    $categories_stmt = run_q($categories_sql);
                    while ($cat_row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($cat_row['cat_id'] == $cat_id) ? 'disabled' : ''; // Disable current category
                        $move_form .= '<option value="' . $cat_row['cat_id'] . '" '.$selected.'>' . htmlspecialchars($cat_row['cat_name']) . '</option>';
                    }

                    $move_form .= '</select>';
                    $move_form .= '<button type="submit">Премести</button>';
                    $move_form .= '</form>';
                } else {
                    $move_form = ''; // Empty if no rights
                }


                echo '<div id="list-topics">' . $del . ' » <a href="topic.php?topic_id=' . $row['topic_id'] . '&cat_id=' . $cat_id . '">' . htmlspecialchars($row['topic_name'], ENT_QUOTES) . '</a> <hr style="border: none;border-bottom: dashed 1px #000000;"><small>Author <b>' . htmlspecialchars($row['username'], ENT_QUOTES) . '</b> With (' . $comment_count . ') Comments</small>' . $move_form . '</div>';

            }
        } else {
            echo '<div id="list-topics">There are no results to display yet</div>';
        }

        if (isset($_SESSION['is_loged'])) {
            echo '<br/><center><a href="operations/add_topic.php?cat_id=' . $cat_id . '"><img src="template/images/add-topic.png" alt="" /></a></center>';
        } else {
            echo '<center>you have to be <a href="login.php">Logged In</a> to create a topic</center>';
        }

    } else {
        echo "Грешка при изпълнение на заявката.";
    }
} else {
    echo 'you have selected a list of topics with no category id';
}

echo '</div>';
echo '</div>';
include 'aside.php';
include 'template/footer.php';

?>