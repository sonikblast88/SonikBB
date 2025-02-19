<?php
require_once 'config.php'; // Включваме config.php, за да имаме достъп до константите

function run_q($sql, $params = []) {
    try {
        // Използваме константите DB_HOST, DB_USERNAME, DB_PASSWORD и DB_NAME
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        if (strpos(strtolower(trim($sql)), "select") === 0) {
            return $stmt;
        } else {
            return true;
        }

    } catch (PDOException $e) {
        die("Connection or query failed: " . $e->getMessage());
    } finally {
        $conn = null;
    }
}

?>