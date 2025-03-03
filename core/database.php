<?php
// database.php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    public $conn;

    public function __construct() {
        $this->connect();
    }

    // Establish a connection to the database using PDO
    public function connect() {
        $this->conn = null;

        try {
            // Build DSN with charset defined in config (DB_CHARSET)
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=" . DB_CHARSET;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
