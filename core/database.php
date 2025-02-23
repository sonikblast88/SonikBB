<?php
// database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'myforum';
    private $username = 'root';
    private $password = '0Tk143n171!';
    public $conn;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
