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
	
    public function updateSignature($userId, $signature) {
        $query = "UPDATE users SET signature = :signature WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':signature', $signature);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
	
    public function uploadAvatar($userId, $file) {
        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $randomFileName = uniqid() . "." . $imageFileType; // Генериране на уникално име
        $targetFile = $targetDir . $randomFileName;
        $uploadOk = 1;

        // Проверка дали файлът е изображение
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return "Файлът не е изображение.";
        }

        // Проверка на размера на файла
        if ($file["size"] > 500000) {
            return "Файлът е твърде голям.";
        }

        // Разрешени формати на файлове
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            return "Разрешени са само JPG, JPEG, PNG & GIF файлове.";
        }

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $avatarPath = $targetFile;
            $this->updateAvatar($userId, $avatarPath);
            return true;
        } else {
            return "Имаше грешка при качването на файла.";
        }
    }

    public function updateAvatar($userId, $avatarPath) {
        $query = "UPDATE users SET avatar = :avatar WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':avatar', $avatarPath);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
	
    public function updatePassword($userId, $hashedPassword) {
        $query = "UPDATE users SET password = :password WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
}