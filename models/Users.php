<?php
// Users.php
class Users {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Метод за извличане на потребител по ID
    public function getUserById($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        // Почистване на входните данни
        $user_id = (int)$user_id;

        // Свързване на параметрите
        $stmt->bindParam(":user_id", $user_id);

        // Изпълнение на заявката
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
	
	// Users.php
	public function getUserByUsername($username) {
		try {
			$query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
			$stmt = $this->conn->prepare($query);

			// Свързване на параметрите
			$stmt->bindParam(":username", $username);

			// Изпълнение на заявката
			$stmt->execute();

			// Проверка за съществуване на резултат
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($user) {
				return $user;
			} else {
				return null; // Или друг подходящ резултат
			}
		} catch (PDOException $e) {
			// Обработка на грешката
			error_log("Грешка при извличане на потребител: " . $e->getMessage());
			return false; // Или хвърляне на изключение
		}
	}
}