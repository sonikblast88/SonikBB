<?php
// $path is defined in functions.php

echo '<div id="aside">';
echo '<div id="profile">';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $type = 'Administrator';
    $stats = '<div id="profile-info"><b>» <a href="' . $path . 'stats.php">Forums Stats</a></b></div>';
} else {
    $stats = '';
    $type = 'User';
}

if (isset($_SESSION['is_loged'])) {
	echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
    echo '<img src="' . $path . $_SESSION['user_info']['avatar'] . '" alt="" id="profile-image" />';
    echo '<div id="profile-info"><b>Name:</b> ' . htmlspecialchars($_SESSION['user_info']['username'], ENT_QUOTES) . '</div>';
    echo '<div id="profile-info"><b>Type:</b> ' . $type . '</div>';
    echo $stats;
    echo '<div id="profile-info"><b>» <a href="' . $path . 'profile.php?profile_id=' . (int)$_SESSION['user_info']['user_id'] . '">Edit Profile</a></b></div>';
    echo '<div id="profile-info"><b>» <a href="' . $path . 'logout.php">Log Out</a></b></div>';
    echo '<div id="profile-info">' . htmlspecialchars($_SESSION['user_info']['signature'], ENT_QUOTES) . '</div>';
} else {
	echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
    echo '<img src="' . WEBSITE . '/uploads/avatar-default.avif" alt="" id="profile-image" />';
    echo '<div id="profile-info">You are not currently logged in. Please <b><a href="login.php">log in</a></b> or <a href="register.php"><b>register</b></a></div>';
}

echo '</div>'; // Close id=profile
echo '<br />';
echo '<div id="last-topics">';
echo '<div id="last-topics-topic-header">» L A S T - T O P I C S</div>';

$sql_last_topics = "SELECT topic_id, topic_name FROM topics WHERE topic_id > 0 ORDER BY topic_id DESC LIMIT 5";
$query2 = run_q($sql_last_topics);

if ($query2) { // Check for successful query result
    $num_results = $query2->rowCount(); // Use rowCount() for PDO
    if ($num_results > 0) {
        while ($row = $query2->fetch(PDO::FETCH_ASSOC)) {
            echo '<div id="last-topics-topic"><b>» </b><a href="' . $path . 'topic.php?topic_id=' . (int)$row['topic_id'] . '">' . htmlspecialchars($row['topic_name'], ENT_QUOTES) . '</a></div>';
        }
    } else {
        echo '<div id="last-topics-topic"><b>There are no recent topics at the moment</b></div>';
    }
} else {
    // Handle error executing the query
    echo '<div id="last-topics-topic"><b>Error retrieving recent topics</b></div>';
}


echo '</div>'; // Close id=last-topics
echo '</div>'; // Close id=aside
?>
