</div> <div id="footer">

<?php
// STATS
// -----------------------------------------------------------------------
$sql_stats = "SELECT COUNT(*) as broi_potrebiteli, (SELECT username FROM users ORDER BY user_id DESC LIMIT 1) as posleden_potrebitel, (SELECT COUNT(*) FROM topics) as broi_temi, (SELECT COUNT(*) FROM comments) as broi_komentari FROM users";
$result = run_q($sql_stats);

if ($result) { // Проверка за успешен резултат
    while ($stats = $result->fetch(PDO::FETCH_ASSOC)) {
        echo '<br/>» We have <b>' . $stats['broi_potrebiteli'] . '</b> registered users.<br/>';
        echo '» They have wrote <b>' . $stats['broi_temi'] . '</b> topics and <b>' . $stats['broi_komentari'] . '</b> comments.<br/>';
        echo '» Last registrated user is: <b>' . htmlspecialchars($stats['posleden_potrebitel'], ENT_QUOTES) . '.</b><br>'; // Escape username
    }
} else {
    echo "Грешка при извличане на статистика."; // Обработка на грешка
}


$date = date('c', time() - 24 * 60 * 60); // Last 24 Hours
$sql_active_users = "SELECT username, type FROM users WHERE last_login > :date";
$params_active_users = [":date" => $date];
$active_users = run_q($sql_active_users, $params_active_users);

if ($active_users) { // Проверка за успешен резултат
    if ($active_users->rowCount() > 0) {
        echo '» Active users last 24 hours: ';
        while ($users = $active_users->fetch(PDO::FETCH_ASSOC)) {
            echo '[ ' . htmlspecialchars($users['username'], ENT_QUOTES) . ' ] '; // Escape username
        }
    } else {
        echo '» There are no active users in the last 24 hours.';
    }
} else {
    echo "Грешка при извличане на активни потребители."; // Обработка на грешка
}


// USER STATISTICS
// -----------------------------------------------------------------------
$ip_address = $_SERVER['REMOTE_ADDR']; // IP адрес
$user_agent = $_SERVER['HTTP_USER_AGENT']; // Браузър и устройство
$referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct'; // Референер (ако няма, е "Direct")
$visit_time = date_create()->format('Y-m-d H:i:s'); // Текущо време
$page_visited = $_SERVER['REQUEST_URI']; // Страница, която е посетена

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    echo '<br />» not saving statistics for administrators';
} else {
    // Използване на prepared statement за запис на статистика
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
        echo "Грешка при запис на статистиката за посетителя."; // Обработка на грешка
    }
}
?>

<br /><hr /><center>SonikBB Version 0.0.18 Dev</center>
</div>

</body>
</html>