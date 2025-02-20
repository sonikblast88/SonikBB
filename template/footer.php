</div>

<div id="footer">

<?php
// STATS
// -----------------------------------------------------------------------
$sql_stats = "SELECT COUNT(*) as user_count, (SELECT username FROM users ORDER BY user_id DESC LIMIT 1) as last_user, (SELECT COUNT(*) FROM topics) as topic_count, (SELECT COUNT(*) FROM comments) as comment_count FROM users";
$result = run_q($sql_stats);

if ($result) { // Check for successful result
    while ($stats = $result->fetch(PDO::FETCH_ASSOC)) {
        echo '<br/>» We have <b>' . $stats['user_count'] . '</b> registered users.<br/>';
        echo '» They have wrote <b>' . $stats['topic_count'] . '</b> topics and <b>' . $stats['comment_count'] . '</b> comments.<br/>';
        echo '» Last registered user is: <b>' . htmlspecialchars($stats['last_user'], ENT_QUOTES) . '.</b><br>'; // Escape username
    }
} else {
    echo "Error retrieving statistics."; // Error handling
}

$date = date('c', time() - 24 * 60 * 60); // Last 24 Hours
$sql_active_users = "SELECT username, type FROM users WHERE last_login > :date";
$params_active_users = [":date" => $date];
$active_users = run_q($sql_active_users, $params_active_users);

if ($active_users) { // Check for successful result
    if ($active_users->rowCount() > 0) {
        echo '» Active users last 24 hours: ';
        while ($users = $active_users->fetch(PDO::FETCH_ASSOC)) {
            echo '[ ' . htmlspecialchars($users['username'], ENT_QUOTES) . ' ] '; // Escape username
        }
    } else {
        echo '» There are no active users in the last 24 hours.';
    }
} else {
    echo "Error retrieving active users."; // Error handling
}

// USER STATISTICS
// -----------------------------------------------------------------------
$ip_address = $_SERVER['REMOTE_ADDR']; // IP address
$user_agent = $_SERVER['HTTP_USER_AGENT']; // Browser and device
$referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct'; // Referrer (if none, it's "Direct")
$visit_time = date_create()->format('Y-m-d H:i:s'); // Current time
$page_visited = $_SERVER['REQUEST_URI']; // Page visited

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    echo '<br />» Not saving statistics for administrators';
} else {
    // Using prepared statement to record statistics
    $sql_insert_visitor = "INSERT INTO visitors (ip_address, user_agent, referrer, visit_time, page_visited) VALUES (:ip_address, :user_agent, :referrer, :visit_time, :page_visited)";
    $params_insert_visitor = [
        ":ip_address" => $ip_address,
        ":user_agent" => $user_agent,
        ":referrer" => $referrer,
        ":visit_time" => $visit_time,
        ":page_visited" => $page_visited,
    ];
    $result_insert = run_q($sql_insert_visitor, $params_insert_visitor);

    if (!$result_insert) {
        echo "Error recording visitor statistics."; // Error handling
    }
}
?>

<hr style="border: 0px;border-top: dotted 1px;"><center>SonikBB Version 0.0.21 Dev</center>
</div>

</body>
</html>
