<?php
include 'functions.php';
include 'template/header.php';

// IF NOT ADMIN REDIRECT - по-добре да се използва exit; след header()
if ((int)$_SESSION['user_info']['type'] !== 2) {
    header('Location: index.php');
    exit; // Важно е да се сложи exit след header()
}

echo '<div id="content">';

// Функции за извличане на данни - използване на prepared statements
function get_daily_unique_visits($date) {
    $sql = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = :date";
    $stmt = run_q($sql, [":date" => $date]); // Използване на prepared statement
    return $stmt->fetchColumn() ?? 0; // По-кратък запис
}

function get_weekly_unique_visits($start_date, $end_date) {
    $sql = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) BETWEEN :start_date AND :end_date";
    $stmt = run_q($sql, [":start_date" => $start_date, ":end_date" => $end_date]);
    return $stmt->fetchColumn() ?? 0;
}

function get_monthly_unique_visits($month, $year) {
    $sql = "SELECT DATE(visit_time) AS visit_date, COUNT(DISTINCT ip_address) AS count 
            FROM visitors 
            WHERE MONTH(visit_time) = :month AND YEAR(visit_time) = :year 
            GROUP BY visit_date";
    $stmt = run_q($sql, [":month" => $month, ":year" => $year]);
    $visits = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $day = date('j', strtotime($row['visit_date']));
        $visits[$day] = $row['count'];
    }
    return $visits;
}

function get_yearly_unique_visits($year) {
    $sql = "SELECT MONTH(visit_time) AS month, COUNT(DISTINCT ip_address) AS count 
            FROM visitors 
            WHERE YEAR(visit_time) = :year 
            GROUP BY month";
    $stmt = run_q($sql, [":year" => $year]);
    $visits = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $visits[$row['month']] = $row['count'];
    }
    return $visits;
}


echo '<h2>Статистика</h2>';

// Дневна статистика
$today = date("Y-m-d");
$daily_visits = get_daily_unique_visits($today); // Използваме новата функция
echo "<p>Дневни посещения ($today): $daily_visits</p>";
echo '<div id="daily_chart" style="width: 100%; height: 300px;"></div>';

// Седмична статистика
$start_date = date("Y-m-d", strtotime("monday this week"));
$end_date = date("Y-m-d", strtotime("sunday this week"));
$weekly_visits = get_weekly_unique_visits($start_date, $end_date); // Използваме новата функция
echo "<p>Седмични посещения ($start_date - $end_date): $weekly_visits</p>";
echo '<div id="weekly_chart" style="width: 100%; height: 300px;"></div>';

// Месечна статистика
$month = date("n");
$year = date("Y");
$monthly_visits = get_monthly_unique_visits($month, $year); // Използваме новата функция
echo '<p>Месечна статистика на посещенията</p>';
echo '<div id="monthly_chart" style="width: 100%; height: 300px;"></div>';

// Годишна статистика
$year = date("Y");
$yearly_visits = get_yearly_unique_visits($year); // Използваме новата функция
echo "<p>Годишни посещения ($year):</p>";
echo '<div id="yearly_chart" style="width: 100%; height: 300px;"></div>';

?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        drawDailyChart();
        drawWeeklyChart();
        drawMonthlyChart();
        drawYearlyChart();
    }

    function drawDailyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Час', 'Посещения', 'Уникални Посетители'], // Добавена колона
            <?php
            $today = date("Y-m-d");
            $daily_visits_data = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = sprintf("%02d", $i);
                $sql_total = "SELECT COUNT(*) FROM visitors WHERE DATE(visit_time) = '$today' AND HOUR(visit_time) = '$hour'";
                $sql_unique = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = '$today' AND HOUR(visit_time) = '$hour'";
                $result_total = run_q($sql_total);
                $result_unique = run_q($sql_unique);
                $count_total = $result_total->fetchColumn() ?? 0; // Използване на fetchColumn()
                $count_unique = $result_unique->fetchColumn() ?? 0; // Използване на fetchColumn()
                $daily_visits_data[$hour] = ['total' => $count_total, 'unique' => $count_unique];
            }
            foreach ($daily_visits_data as $hour => $counts) {
                echo "['$hour', " . $counts['total'] . ", " . $counts['unique'] . "],";
            }
            ?>
        ]);

        var options = {
            title: 'Дневна статистика на посещенията',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('daily_chart'));
        chart.draw(data, options);
    }

function drawWeeklyChart() {
    var data = google.visualization.arrayToDataTable([
        ['Ден', 'Посещения', 'Уникални Посетители'],
        <?php
        $start_date = date("Y-m-d", strtotime("monday this week"));
        $end_date = date("Y-m-d", strtotime("sunday this week"));
        $weekly_visits_data = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($start_date . ' + ' . $i . ' days'));
            $sql_total = "SELECT COUNT(*) FROM visitors WHERE DATE(visit_time) = '$date'";
            $sql_unique = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = '$date'";
            $result_total = run_q($sql_total);
            $result_unique = run_q($sql_unique);
            $count_total = $result_total->fetchColumn() ?? 0;
            $count_unique = $result_unique->fetchColumn() ?? 0;
            $day_name = date('l', strtotime($date));
            $weekly_visits_data[$day_name] = ['total' => $count_total, 'unique' => $count_unique];
        }
        foreach ($weekly_visits_data as $day => $counts) {
            echo "['$day', " . $counts['total'] . ", " . $counts['unique'] . "],";
        }
        ?>
    ]);

    var options = {
        title: 'Седмична статистика на посещенията',
        curveType: 'function',
        legend: { position: 'bottom' }
    };

    var chart = new google.visualization.LineChart(document.getElementById('weekly_chart'));
    chart.draw(data, options); // <--- Add this line
}
		
function drawMonthlyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Ден', 'Посещения', 'Уникални Посетители'],
            <?php
            $month = date("n");
            $year = date("Y");
            $visits = get_monthly_unique_visits($month, $year);
            foreach ($visits as $day => $count) {
                $sql_total = "SELECT COUNT(*) FROM visitors WHERE DATE(visit_time) = '$year-$month-$day'";
                $sql_unique = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = '$year-$month-$day'";
                $result_total = run_q($sql_total);
                $result_unique = run_q($sql_unique);
                $count_total = $result_total->fetchColumn() ?? 0;
                $count_unique = $result_unique->fetchColumn() ?? 0;

                echo "['$day', $count_total, $count_unique],";
            }
            ?>
        ]);

        var options = {
            title: 'Месечна статистика на посещенията',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('monthly_chart'));
        chart.draw(data, options);
    }

    function drawYearlyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Месец', 'Посещения', 'Уникални Посетители'],
            <?php
            $year = date("Y");
            $yearly_visits = get_yearly_unique_visits($year);
            foreach ($yearly_visits as $month => $count) {
                $sql_total = "SELECT COUNT(*) FROM visitors WHERE YEAR(visit_time) = '$year' AND MONTH(visit_time) = '$month'";
                $sql_unique = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE YEAR(visit_time) = '$year' AND MONTH(visit_time) = '$month'";
                $result_total = run_q($sql_total);
                $result_unique = run_q($sql_unique);
                $count_total = $result_total->fetchColumn() ?? 0;
                $count_unique = $result_unique->fetchColumn() ?? 0;
                echo "['" . date("F", mktime(0, 0, 0, $month, 1, $year)) . "', $count_total, $count_unique],";
            }
            ?>
        ]);

        var options = {
            title: 'Годишна статистика на посещенията',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('yearly_chart'));
        chart.draw(data, options);
    }
</script>

<?php
// ... (кодът за графиките от предишния отговор)

// Обработка на изтриването (използване на prepared statement)
if (isset($_POST['delete_ip']) || isset($_GET['ip'])) {
    $ip_address_to_delete = isset($_POST['ip_address']) ? $_POST['ip_address'] : $_GET['ip'];

    if (filter_var($ip_address_to_delete, FILTER_VALIDATE_IP)) {
        $delete_sql = "DELETE FROM visitors WHERE ip_address = :ip_address";
        $delete_query = run_q($delete_sql, [":ip_address" => $ip_address_to_delete]);

        if ($delete_query) {
            echo '<p style="color: green;">Записи с IP адрес ' . $ip_address_to_delete . ' бяха изтрити успешно.</p>';
            redirect('stats.php');
        } else {
            echo '<p style="color: red;">Грешка при изтриване на записи.</p>';
        }
    } else {
        echo '<p style="color: red;">Невалиден IP адрес.</p>';
    }
}

// Извличане на данни за посетители (използване на prepared statement)
$sql = "SELECT v1.* FROM visitors v1 
        INNER JOIN (SELECT ip_address, MAX(visit_time) AS last_visit_time FROM visitors GROUP BY ip_address ORDER BY last_visit_time DESC LIMIT 100) v2 
        ON v1.ip_address = v2.ip_address AND v1.visit_time = v2.last_visit_time 
        ORDER BY v2.last_visit_time DESC";

$query = run_q($sql);

?>

<form method="post" action="">
    <label for="ip_address">IP адрес за изтриване:</label>
    <input type="text" name="ip_address" id="ip_address">
    <button type="submit" name="delete_ip">Изтрий</button>
</form>

<?php

if ($query && $query->rowCount() > 0) { // Проверка дали има резултати от заявката
    echo '<h2>Последни 100 посетители</h2>';
    echo '<table border="1" cellpadding="10" cellspacing="0" width="100%">';
    echo '<tr>
            <th>ID</th>
            <th>IP адрес</th>
            <th>Браузър</th>
            <th>Референер</th>
            <th>Време на посещение</th>
            <th>Страница</th>
        </tr>';

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) { // Използване на fetchAssoc() за по-лесен достъп до данните
        echo '<tr>
                <td>' . $row['id'] . '</td>
                <td><a href="?ip=' . $row['ip_address'] . '" onclick="return confirm(\'Сигурни ли сте, че искате да изтриете записи с IP адрес ' . $row['ip_address'] . '?\');">' . $row['ip_address'] . '</a></td>
                <td>' . htmlspecialchars($row['user_agent']) . '</td> <td>' . ($row['referrer'] ? htmlspecialchars($row['referrer']) : 'Direct') . '</td>
                <td>' . $row['visit_time'] . '</td>
                <td>' . htmlspecialchars($row['page_visited']) . '</td>
            </tr>';
    }

    echo '</table>';
} else {
    echo '<p>Няма данни за посетители.</p>';
}

echo '</div>';

include 'aside.php';
include 'template/footer.php';
?>