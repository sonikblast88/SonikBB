<div id="aside">
    <div id="profile">
        <?php
        // Default profile type and stats info
        $type = '<div id="profile-info"><b>Type:</b> USER</div>';
        $stats = '';

        // If the user is logged in, display their profile info
        if (isset($_SESSION['is_loged'])) {
            // If the user is an administrator, update type and show stats link
            if ($_SESSION['type'] == 2) {
                $type = '<div id="profile-info"><b>Type:</b> Administrator</div>';
                $stats = '<div id="profile-info"><b>» <a href="stats.php">Forums Stats</a></b></div>';
            }

            echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
            echo '<img src="' . htmlspecialchars($_SESSION['avatar']) . '" alt="" id="profile-image" />';
            echo '<div id="profile-info"><b>Name:</b> ' . htmlspecialchars($_SESSION['username']) . '</div>';
            echo $type;
            echo $stats;
            echo '<div id="profile-info"><b>» <a href="profile.php?profile_id=' . (int)$_SESSION['user_id'] . '">Edit Profile</a></b></div>';
            echo '<div id="profile-info"><b>» <a href="logout.php">Log Out</a></b></div>';
            echo '<div id="profile-info">' . htmlspecialchars($_SESSION['signature']) . '</div>';
        } else {
            // If not logged in, display default profile info
            echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
            echo '<img src="template/images/avatar-default.avif" alt="" id="profile-image" />';
            echo '<div id="profile-info">You are not currently logged in. Please <b><a href="login.php">log in</a></b> or <a href="register.php"><b>register</b></a></div>';
        }
        ?>
    </div>

    <br />
    <div id="last-topics">
        <div id="last-topics-topic-header">» L A S T - T O P I C S</div>
        <?php
        // Retrieve and display the last topics
		$lastTopics = $topicsModel->getLastTopics();
		while ($row = $lastTopics->fetch(PDO::FETCH_ASSOC)): ?>
			<div id="last-topics-topic">
				📝 <a href="topic.php?topic_id=<?= $row['topic_id'] ?>">
					<?= htmlspecialchars($row['topic_name']) ?>
				</a> (<?= htmlspecialchars($row['category_name']) ?>)  
				<span class="topic-date">📅 <?= date("d M Y H:i", strtotime($row['date_added_topic'])) ?></span>
			</div>
		<?php endwhile; ?>
    </div>
</div>
