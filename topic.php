<?php

include 'functions.php';
include 'template/header.php';

$Parsedown = new Parsedown();

$topic_id = (int)filter_input(INPUT_GET, 'topic_id');
$cat_id = (int)filter_input(INPUT_GET, 'cat_id');

if ($topic_id > 0) {
    $sql = "SELECT topic_id, topic_name, topic_desc, topic_author, user_id, username, signature, type, avatar FROM topics, users WHERE topic_id = :topic_id AND user_id = topic_author";
    $params = [":topic_id" => $topic_id];
    $stmt = run_q($sql, $params);

    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_type = ($row['type'] == 1) ? 'Потребител' : (($row['type'] == 2) ? 'Администратор' : '');

            $del1 = '';
            $ed1 = '';
            if (isset($_SESSION['is_loged']) && ($_SESSION['user_info']['user_id'] == $row['topic_author'] || $_SESSION['user_info']['type'] == 2)) {
                $del1 = '<div id = "profile-info">[ <a href="operations/del_topic.php?topic_id=' . $row['topic_id'] . '&cat_id=' . $cat_id . '" onclick="return confirm(\'Are you sure you want to delete this topic?\')">Изтрии</a> ]</div>';
                $ed1 = '<div id = "profile-info">[ <a href="operations/edit_topic.php?topic_id=' . $topic_id . '&cat_id=' . $cat_id . '">Редактирай</a> ]</div>';
            }

            $markdownText = $row['topic_desc'];
            $htmlText = $Parsedown->text($markdownText);

            echo '<div style="width: 92%; border: 1px solid black; margin: 0 auto;padding:15px;padding-top: 0px;margin-top: 20px;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;overflow: hidden;">';
            echo '<h2>» ' . htmlspecialchars($row['topic_name'], ENT_QUOTES) . '</h2>';

            // PROFILE PART START
            echo '<div style="width: 99%; border: 1px solid black; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;box-shadow: 0 0 8px rgba(0, 0, 0, .8);border-radius: 5px;">';
            echo '<div style="display: flex; align-items: center;">';
            echo '<img src="' . $path . $row['avatar'] . '" alt="" id="post-profile-image" style="margin-right: 10px; max-width: 150px; max-height: 150px;" />'; // Ограничаване на размера на аватара
            echo '<div>';
            echo '<div>Username: ' . htmlspecialchars($row['username'], ENT_QUOTES) . '</div>';
            echo '<div>Type: ' . $user_type . '</div>';
            echo '<div>Signature: ' . htmlspecialchars($row['signature'], ENT_QUOTES) . '</div>';
            echo '</div>';
            echo '</div>';
            echo '<div style="display: flex; flex-direction: column; padding-right: 10px;">';
            echo $del1;
            echo $ed1;
            echo '</div>';
            echo '</div>';
            // PROFILE PART END

            echo $htmlText;
            echo '</div>';

            if (isset($_SESSION['is_loged'])) {
                echo '<br/><center><a href = "operations/add_comment.php?topic_id=' . $topic_id . '&cat_id=' . $cat_id . '"><img src = "template/images/comment.png" alt = "" /></a><br /><br></center>';
            } else {
                echo '<center> --------------------------------- </center>';
            }
        }
    } else {
        echo "Грешка при изпълнение на заявката.";
    }
}

include 'comments.php';
include 'template/footer.php';

?>