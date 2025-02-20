<?php
// $path е дефиниран във functions.php

echo '<div id="aside">';
echo '<div id="profile">';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $type = 'Администратор';
    $stats = '<div id="profile-info"><b>» <a href="' . $path . 'stats.php">Forums Stats</a></b></div>';
} else {
    $stats = '';
    $type = 'Потребител';
}

if (isset($_SESSION['is_loged'])) {
    echo '<img src="' . $path . $_SESSION['user_info']['avatar'] . '" alt="" id="profile-image" />';
    echo '<div id="profile-info"><b>Име:</b> ' . htmlspecialchars($_SESSION['user_info']['username'], ENT_QUOTES) . '</div>';
    echo '<div id="profile-info"><b>Тип:</b> ' . $type . '</div>';
    echo $stats;
    echo '<div id="profile-info"><b>» <a href="' . $path . 'profile.php?profile_id=' . (int)$_SESSION['user_info']['user_id'] . '">Редакция на профил</a></b></div>';
    echo '<div id="profile-info"><b>» <a href="' . $path . 'logout.php">Изход от системата</a></b></div>';
    echo '<div id="profile-info">' . htmlspecialchars($_SESSION['user_info']['signature'], ENT_QUOTES) . '</div>';
} else {
    echo '<img src="' . $path . 'template/images/avatar-default.avif" alt="" id="profile-image" />';
    echo '<div id="profile-info">В момента не сте регистриран моля <b><a href="login.php">впишете се</a></b> или се <a href="register.php"><b>регистрирайте</b></a></div>';
}

echo '</div>'; // Затварям id=profile
echo '<br />';
echo '<div id="last-topics">';
echo '<div id="last-topics-topic-header">» П О С Л Е Д Н И - Т Е М И</div>';

// Използване на prepared statement
$sql_last_topics = "SELECT topic_id, topic_name FROM topics WHERE topic_id > 0 ORDER BY topic_id DESC LIMIT 5";
$query2 = run_q($sql_last_topics);

if ($query2) { // Проверка за успешен резултат от заявката
    $num_results = $query2->rowCount(); // Използваме rowCount() за PDO
    if ($num_results > 0) {
        while ($row = $query2->fetch(PDO::FETCH_ASSOC)) {
            echo '<div id="last-topics-topic"><b>» </b><a href="' . $path . 'topic.php?topic_id=' . (int)$row['topic_id'] . '">' . htmlspecialchars($row['topic_name'], ENT_QUOTES) . '</a></div>';
        }
    } else {
        echo '<div id="last-topics-topic"><b>В момента няма последни теми</b></div>';
    }
} else {
    // Обработка на грешката при изпълнение на заявката
    echo '<div id="last-topics-topic"><b>Грешка при извличане на последните теми</b></div>';
}


echo '</div>'; // Затварям id=last-topics
echo '</div>'; // Затварям id=aside
?>