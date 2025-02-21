<?php
include 'functions.php';
include 'template/header.php';

// IF NOT ADMIN REDIRECT - better to use exit; after header()
if ((int)$_SESSION['user_info']['type'] !== 2) {
    header('Location: index.php');
    exit; // It's important to use exit after header()
}

echo '<div id="content">';

// Functions for fetching data - using prepared statements
function get_daily_unique_visits($date) {
    $sql = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = :date";
    $stmt = run_q($sql, [":date" => $date]); // Using prepared statement
    return $stmt->fetchColumn() ?? 0; // Shorter syntax
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

echo '<h2>Statistics</h2>';

// Daily statistics
$today = date("Y-m-d");
$daily_visits = get_daily_unique_visits($today); // Using the new function
echo "<p>Daily visits ($today): $daily_visits</p>";
echo '<div id="daily_chart" style="width: 100%; height: 300px;"></div>';

// Weekly statistics
$start_date = date("Y-m-d", strtotime("monday this week"));
$end_date = date("Y-m-d", strtotime("sunday this week"));
$weekly_visits = get_weekly_unique_visits($start_date, $end_date); // Using the new function
echo "<p>Weekly visits ($start_date - $end_date): $weekly_visits</p>";
echo '<div id="weekly_chart" style="width: 100%; height: 300px;"></div>';

// Monthly statistics
$month = date("n");
$year = date("Y");
$monthly_visits = get_monthly_unique_visits($month, $year); // Using the new function
echo '<p>Monthly visit statistics</p>';
echo '<div id="monthly_chart" style="width: 100%; height: 300px;"></div>';

// Yearly statistics
$year = date("Y");
$yearly_visits = get_yearly_unique_visits($year); // Using the new function
echo "<p>Yearly visits ($year):</p>";
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
            ['Hour', 'Visits', 'Unique Visitors'], // Added column
            <?php
            $today = date("Y-m-d");
            $daily_visits_data = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = sprintf("%02d", $i);
                $sql_total = "SELECT COUNT(*) FROM visitors WHERE DATE(visit_time) = '$today' AND HOUR(visit_time) = '$hour'";
                $sql_unique = "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = '$today' AND HOUR(visit_time) = '$hour'";
                $result_total = run_q($sql_total);
                $result_unique = run_q($sql_unique);
                $count_total = $result_total->fetchColumn() ?? 0; // Using fetchColumn()
                $count_unique = $result_unique->fetchColumn() ?? 0; // Using fetchColumn()
                $daily_visits_data[$hour] = ['total' => $count_total, 'unique' => $count_unique];
            }
            foreach ($daily_visits_data as $hour => $counts) {
                echo "['$hour', " . $counts['total'] . ", " . $counts['unique'] . "],";
            }
            ?>
        ]);

        var options = {
            title: 'Daily Visit Statistics',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('daily_chart'));
        chart.draw(data, options);
    }

    function drawWeeklyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Day', 'Visits', 'Unique Visitors'],
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
            title: 'Weekly Visit Statistics',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('weekly_chart'));
        chart.draw(data, options);
    }

    function drawMonthlyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Day', 'Visits', 'Unique Visitors'],
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
            title: 'Monthly Visit Statistics',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('monthly_chart'));
        chart.draw(data, options);
    }

    function drawYearlyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Month', 'Visits', 'Unique Visitors'],
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
            title: 'Yearly Visit Statistics',
            curveType: 'function',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('yearly_chart'));
        chart.draw(data, options);
    }
</script>

<?php
// ... (code for charts from the previous response)

// Handling deletion (using prepared statement)
if (isset($_POST['delete_ip']) || isset($_GET['ip'])) {
    $ip_address_to_delete = isset($_POST['ip_address']) ? $_POST['ip_address'] : $_GET['ip'];

    if (filter_var($ip_address_to_delete, FILTER_VALIDATE_IP)) {
        $delete_sql = "DELETE FROM visitors WHERE ip_address = :ip_address";
        $delete_query = run_q($delete_sql, [":ip_address" => $ip_address_to_delete]);

        if ($delete_query) {
            echo '<p style="color: green;">Records with IP address ' . $ip_address_to_delete . ' were deleted successfully.</p>';
            redirect('stats.php');
        } else {
            echo '<p style="color: red;">Error deleting records.</p>';
        }
    } else {
        echo '<p style="color: red;">Invalid IP address.</p>';
    }
}

// Fetching visitor data (using prepared statement)
$sql = "SELECT v1.* FROM visitors v1 
        INNER JOIN (SELECT ip_address, MAX(visit_time) AS last_visit_time FROM visitors GROUP BY ip_address ORDER BY last_visit_time DESC LIMIT 100) v2 
        ON v1.ip_address = v2.ip_address AND v1.visit_time = v2.last_visit_time 
        ORDER BY v2.last_visit_time DESC";

$query = run_q($sql);

?>

<form method="post" action="">
    <label for="ip_address">IP address to delete:</label>
    <input type="text" name="ip_address" id="ip_address">
    <button type="submit" name="delete_ip">Delete</button>
</form>

<?php

if ($query && $query->rowCount() > 0) {
    echo '<h2>Last 100 Visitors</h2>';
    echo '<div class="table-container">';
    echo '<table class="visitor-table">';
    echo '<thead>
            <tr>
                <th>IP Address</th>
                <th>Browser</th>
                <th>Referrer</th>
                <th>Visit Time</th>
                <th>Page</th>
            </tr>
          </thead>
          <tbody>';

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>
                <td><a href="?ip=' . $row['ip_address'] . '" onclick="return confirm(\'Are you sure you want to delete records with IP address ' . $row['ip_address'] . '?\');">' . $row['ip_address'] . '</a></td>
                <td>' . htmlspecialchars($row['user_agent']) . '</td>
                <td>' . ($row['referrer'] ? htmlspecialchars($row['referrer']) : 'Direct') . '</td>
                <td>' . $row['visit_time'] . '</td>
                <td>' . htmlspecialchars($row['page_visited']) . '</td>
              </tr>';
    }

    echo '</tbody></table></div>';
} else {
    echo '<p>No visitor data available.</p>';
}

echo '</div>';
include 'aside.php';
include 'template/footer.php';
?>

<style>
    .table-container {
        overflow-x: auto;
        margin: 20px 0;
    }

    .visitor-table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 13px; /* Smaller font size */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .visitor-table th,
    .visitor-table td {
        padding: 8px 10px; /* Less padding */
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .visitor-table th {
        background-color: #f8f9fa;
        font-weight: bold;
        color: #333;
    }

    .visitor-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .visitor-table a {
        color: #007bff;
        text-decoration: none;
    }

    .visitor-table a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .visitor-table {
            font-size: 12px;
        }

        .visitor-table th,
        .visitor-table td {
            padding: 6px 8px;
        }
    }
</style>
