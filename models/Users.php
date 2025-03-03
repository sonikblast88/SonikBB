<?php
// models/Users.php
class Users {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to retrieve a user by ID
    public function getUserById($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $user_id = (int)$user_id;

        // Bind parameters
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
	
    // Method to retrieve a user by username
    public function getUserByUsername($username) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(":username", $username);

            // Execute the query
            $stmt->execute();

            // Check if a result exists
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return $user;
            } else {
                return null; // Or another appropriate result
            }
        } catch (PDOException $e) {
            // Handle error
            error_log("Error retrieving user: " . $e->getMessage());
            return false; // Or throw an exception
        }
    }
	
    // Method to update a user's signature
    public function updateSignature($userId, $signature) {
        $query = "UPDATE users SET signature = :signature WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':signature', $signature);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
	
    // Method to handle avatar upload and update
    public function uploadAvatar($userId, $file) {
        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $randomFileName = uniqid() . "." . $imageFileType; // Generate a unique file name
        $targetFile = $targetDir . $randomFileName;
        $uploadOk = 1;

        // Check if the file is an image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return "The file is not an image.";
        }

        // Check file size
        if ($file["size"] > 500000) {
            return "The file is too large.";
        }

        // Allowed file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            return "Only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $avatarPath = $targetFile;
            $this->updateAvatar($userId, $avatarPath);
            return true;
        } else {
            return "There was an error uploading the file.";
        }
    }

    // Method to update a user's avatar path
    public function updateAvatar($userId, $avatarPath) {
        $query = "UPDATE users SET avatar = :avatar WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':avatar', $avatarPath);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
	
    // Method to update a user's password
    public function updatePassword($userId, $hashedPassword) {
        $query = "UPDATE users SET password = :password WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
	
    // Method to update a user's last login timestamp
    public function updateLastLogin($user_id) {
        $query = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
