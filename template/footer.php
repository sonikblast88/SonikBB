</div>

<div id="footer">

<?php

// Създаване на нова инстанция на Database
$db = new Database();
$conn = $db->connect();

// Проверка за успешна връзка
if (!$conn) {
    die("Database connection error.");
}

// Получаване на статистики
$sql_stats = "SELECT COUNT(*) as user_count, 
                     (SELECT username FROM users ORDER BY user_id DESC LIMIT 1) as last_user, 
                     (SELECT COUNT(*) FROM topics) as topic_count, 
                     (SELECT COUNT(*) FROM comments) as comment_count 
              FROM users";
$stmt = $conn->query($sql_stats);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

if ($stats) {
    echo '<br/>» We have <b>' . $stats['user_count'] . '</b> registered users.<br/>';
    echo '» They have written <b>' . $stats['topic_count'] . '</b> topics and <b>' . $stats['comment_count'] . '</b> comments.<br/>';
    echo '» Last registered user is: <b>' . htmlspecialchars($stats['last_user'], ENT_QUOTES) . '.</b><br>';
} else {
    echo "Error retrieving statistics.";
}

// Активни потребители в последните 24 часа
$date = date('Y-m-d H:i:s', time() - 24 * 60 * 60);
$sql_active_users = "SELECT username FROM users WHERE last_login > :date";
$stmt = $conn->prepare($sql_active_users);
$stmt->execute([':date' => $date]);
$active_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '» Active users last 24 hours: ';
if ($active_users) {
    foreach ($active_users as $user) {
        echo '[ ' . htmlspecialchars($user['username'], ENT_QUOTES) . ' ] ';
    }
} else {
    echo 'No active users in the last 24 hours.';
}

// Запазване на посетителски статистики (изключва администратори)
if (!isset($_SESSION['is_loged']) || $_SESSION['type'] != 2) {
    $sql_insert_visitor = "INSERT INTO visitors (ip_address, user_agent, referrer, visit_time, page_visited) 
                            VALUES (:ip_address, :user_agent, :referrer, :visit_time, :page_visited)";
    $stmt = $conn->prepare($sql_insert_visitor);
    $stmt->execute([
        ':ip_address' => $_SERVER['REMOTE_ADDR'],
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ':referrer' => $_SERVER['HTTP_REFERER'] ?? 'Direct',
        ':visit_time' => date('Y-m-d H:i:s'),
        ':page_visited' => $_SERVER['REQUEST_URI']
    ]);
}

$uptime_output = shell_exec('uptime -p');

if ($uptime_output) {
    echo '<br/>» Server Uptime: <b>' . htmlspecialchars(trim($uptime_output), ENT_QUOTES) . '</b><br/>';
} else {
    echo '<br/>» Server Uptime: Cannot be requested!<br/>';
}


?>
<hr style="border: 0px;border-top: dotted 1px;">
<center>SonikBB Version 0.1.11 Dev</center>
</div>
</body>
</html>
