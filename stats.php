<?php
session_start();
include_once 'core/autoload.php';
include_once 'models/Users.php';
include_once 'models/Stats.php';
include 'template/header.php';

$database = new Database();
$db = $database->connect();

if (!isAdmin()) {
    echo "You don't have rights to view this page.";
    exit;
}

$stats = new Stats($db);
$recentVisitors = $stats->getRecentVisitors();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ip'])) {
    if ($stats->deleteIP($_POST['ip_address'])) {
        echo "<p style='color: green;'>IP deleted successfully.</p>";
        header("Location: stats.php");
        exit();
    } else {
        echo "<p style='color: red;'>Invalid IP.</p>";
    }
}

?>
<div class="topic-container">
<h2>Statistics</h2>
<div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
    <div style="width: 48%;"><canvas id="dailyChart"></canvas></div>
    <div style="width: 48%;"><canvas id="weeklyChart"></canvas></div>
    <div style="width: 48%;"><canvas id="monthlyChart"></canvas></div>
    <div style="width: 48%;"><canvas id="yearlyChart"></canvas></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    function createChart(id, data, labels, labelTotal, labelUnique, type = 'bar') {
        new Chart(document.getElementById(id).getContext('2d'), {
            type: type,
            data: {
                labels: labels,
                datasets: [
                    { label: labelTotal, data: data.map(d => d.total), backgroundColor: 'rgba(54, 162, 235, 0.6)' },
                    { label: labelUnique, data: data.map(d => d.unique), backgroundColor: 'rgba(255, 99, 132, 0.6)' }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    createChart('dailyChart', <?= $stats->getDailyStats() ?>, <?= json_encode(range(0, 23)) ?>, 'Total Visits', 'Unique Visits', 'line');
    createChart('weeklyChart', <?= $stats->getWeeklyStats() ?>, <?= json_encode(array_column(json_decode($stats->getWeeklyStats(), true), 'day')) ?>, 'Total Visits', 'Unique Visits');
    createChart('monthlyChart', <?= $stats->getMonthlyStats() ?>, <?= json_encode(range(1, date("t"))) ?>, 'Total Visits', 'Unique Visits');
    createChart('yearlyChart', <?= $stats->getYearlyStats() ?>, <?= json_encode(array_column(json_decode($stats->getYearlyStats(), true), 'month')) ?>, 'Total Visits', 'Unique Visits');
});
</script>

<h2>Last 42 Visitors</h2>
<form method="post">
    <label for="ip_address">Delete IP:</label>
    <input type="text" name="ip_address" required>
    <button type="submit" name="delete_ip">Delete</button>
</form>

<!-- Таблица с последните 42 посетителя -->
<div class="table-container">
    <table class="visitor-table">
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Browser</th>
                <th>Referrer</th>
                <th>Visit Time</th>
                <th>Page</th>
                <th>Is Bot</th>
                <th>Country</th>
                <th>City</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recentVisitors)): ?>
                <?php foreach ($recentVisitors as $row): ?>
                    <?php 
                        $bot_name = $stats->detectBot($row['user_agent']);
                        $row_class = $bot_name !== 'No' ? 'class="bot-row"' : '';
						$country = isset($row['country']) ? $row['country'] : 'Unknown';
						$city = isset($row['city']) ? $row['city'] : 'Unknown';

                    ?>
                    <tr <?= $row_class ?>>
                        <td><?= htmlspecialchars($row['ip_address']) ?></td>
                        <td><?= htmlspecialchars($row['user_agent']) ?></td>
                        <td><?= $row['referrer'] ? htmlspecialchars($row['referrer']) : 'Direct' ?></td>
                        <td><?= htmlspecialchars($row['visit_time']) ?></td>
                        <td><?= htmlspecialchars($row['page_visited']) ?></td>
                        <td><?= $bot_name ?></td>
                        <td><?= $country ?></td>
                        <td><?= $city ?></td>
                        <td>
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="ip_address" value="<?= htmlspecialchars($row['ip_address']) ?>">
                                <button type="submit" name="delete_ip" onclick="return confirm('Are you sure?');">❌</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">No visitor data available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
<?php include 'template/footer.php'; ?>

<style>
    .table-container {
        overflow-x: auto;
        margin: 20px 0;
    }

    .visitor-table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 14px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .visitor-table th, .visitor-table td {
        padding: 8px 10px;
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

    .visitor-table td {
        word-break: break-word;
    }

    .visitor-table .bot-row {
        background-color: #fff3cd;
    }

    @media (max-width: 768px) {
        .visitor-table {
            font-size: 12px;
        }
    }

    button {
        background: none;
        border: none;
        color: red;
        cursor: pointer;
        font-size: 16px;
    }
</style>
