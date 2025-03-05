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
            $data[] = [
                "hour" => sprintf("%02d:00", $i),
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
            $stmt = $this->runQuery("DELETE FROM visitors WHERE ip_address = ?", [$ip]);
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    public function getRecentVisitors($limit = 42) {
        $sql = "SELECT * FROM visitors ORDER BY visit_time DESC LIMIT $limit";
        $stmt = $this->runQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
	public function getGeoInfo($ip) {
		session_start(); // Ensure that sessions are started

		// If we already have a cached result for this IP, return it
		if (isset($_SESSION['geo_cache'][$ip])) {
			return $_SESSION['geo_cache'][$ip];
		}

		// Validate the IP address
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return ['country' => 'Invalid IP', 'city' => 'Invalid IP'];
		}

		$url = "http://ip-api.com/json/{$ip}?fields=status,country,city";
		$response = @file_get_contents($url);
		
		if ($response) {
			$data = json_decode($response, true);
			if ($data['status'] === 'success') {
				$_SESSION['geo_cache'][$ip] = [  // Store in session cache
					'country' => $data['country'] ?? 'Unknown',
					'city' => $data['city'] ?? 'Unknown'
				];
				return $_SESSION['geo_cache'][$ip];
			}
		}

		return ['country' => 'Unknown', 'city' => 'Unknown'];
	}
public function getOSStats() {
    $stmt = $this->runQuery("SELECT user_agent FROM visitors");
    $osCounts = [
        "Windows" => 0, 
        "MacOS" => 0, 
        "Linux" => 0, 
        "Android" => 0, 
        "iOS" => 0, 
        "Other" => 0
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ua = strtolower($row['user_agent']);
        if (strpos($ua, 'windows') !== false) {
            $osCounts["Windows"]++;
        } elseif (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac os') !== false) {
            $osCounts["MacOS"]++;
        } elseif (strpos($ua, 'linux') !== false && strpos($ua, 'android') === false) {
            $osCounts["Linux"]++;
        } elseif (strpos($ua, 'android') !== false) {
            $osCounts["Android"]++;
        } elseif (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'ios') !== false) {
            $osCounts["iOS"]++;
        } else {
            $osCounts["Other"]++;
        }
    }

    return json_encode($osCounts);
}
public function getHourlyTraffic() {
    $stmt = $this->runQuery("
        SELECT HOUR(visit_time) as hour, COUNT(*) as visits 
        FROM visitors 
        WHERE DATE(visit_time) = CURDATE() 
        GROUP BY hour 
        ORDER BY hour
    ");
    
    $data = array_fill(0, 24, 0); // Initialize array with 24 hours
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$row['hour']] = $row['visits'];
    }

    return json_encode($data); // Return as JSON
}

}
?>
