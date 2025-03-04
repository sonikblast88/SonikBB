<?php
class Stats {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function runQuery($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    private function fetchCount($query, $params = []) {
        return $this->runQuery($query, $params)->fetchColumn() ?? 0;
    }

    public function getDailyStats() {
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $hour = sprintf("%02d", $i);
            $data[] = [
                "hour" => "$hour:00",
                "total" => $this->fetchCount("SELECT COUNT(*) FROM visitors WHERE DATE(visit_time) = CURDATE() AND HOUR(visit_time) = ?", [$i]),
                "unique" => $this->fetchCount("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = CURDATE() AND HOUR(visit_time) = ?", [$i])
            ];
        }
        return json_encode($data);
    }

    public function getWeeklyStats() {
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date("Y-m-d", strtotime("-$i days"));
            $data[] = [
                "day" => date("l", strtotime($date)),
                "total" => $this->fetchCount("SELECT COUNT(*) FROM visitors WHERE DATE(visit_time) = ?", [$date]),
                "unique" => $this->fetchCount("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DATE(visit_time) = ?", [$date])
            ];
        }
        return json_encode($data);
    }

    public function getMonthlyStats() {
        $data = [];
        for ($i = 1; $i <= date("t"); $i++) {
            $data[] = [
                "day" => $i,
                "total" => $this->fetchCount("SELECT COUNT(*) FROM visitors WHERE DAY(visit_time) = ?", [$i]),
                "unique" => $this->fetchCount("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE DAY(visit_time) = ?", [$i])
            ];
        }
        return json_encode($data);
    }

    public function getYearlyStats() {
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = [
                "month" => date("F", mktime(0, 0, 0, $i, 1)),
                "total" => $this->fetchCount("SELECT COUNT(*) FROM visitors WHERE MONTH(visit_time) = ?", [$i]),
                "unique" => $this->fetchCount("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE MONTH(visit_time) = ?", [$i])
            ];
        }
        return json_encode($data);
    }

    public function getTopPages() {
        $stmt = $this->runQuery("SELECT page_visited, COUNT(*) as visits FROM visitors GROUP BY page_visited ORDER BY visits DESC LIMIT 5");
        return json_encode($stmt->fetchAll(PDO::FETCH_KEY_PAIR));
    }

    public function getTrafficStats() {
        $stmt = $this->runQuery("SELECT user_agent FROM visitors");
        $botCount = 0;
        $humanCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->detectBot($row["user_agent"]) !== "No" ? $botCount++ : $humanCount++;
        }
        return json_encode(["Bots" => $botCount, "Humans" => $humanCount]);
    }

    public function detectBot($user_agent) {
        $bots = ['googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot'];
        foreach ($bots as $bot) {
            if (stripos($user_agent, $bot) !== false) {
                return ucfirst($bot) . " Bot";
            }
        }
        return "No";
    }

    public function deleteIP($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $stmt = $this->runQuery("DELETE FROM visitors WHERE ip_address = :ip_address", [":ip_address" => $ip]);
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    public function getRecentVisitors($limit = 42) {
        $sql = "SELECT * FROM visitors ORDER BY visit_time DESC LIMIT :limit";
        $stmt = $this->runQuery($sql, [":limit" => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
