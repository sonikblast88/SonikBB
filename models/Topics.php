<?php
// models/Topics.php
class Topics {
    private $conn;
    private $table_name = "topics";

    public $topic_id;
    public $parent;
    public $topic_name;
    public $topic_desc;
    public $topic_author;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Метод за създаване на тема
    public function createTopic($parent, $topic_name, $topic_desc, $topic_author) {
        $query = "INSERT INTO " . $this->table_name . " (parent, topic_name, topic_desc, topic_author, date_added_topic) VALUES (:parent, :topic_name, :topic_desc, :topic_author, now())";
        $stmt = $this->conn->prepare($query);

        // Почистване на входните данни
        $parent = (int)$parent;
        $topic_name = htmlspecialchars(strip_tags($topic_name));
        $topic_desc = $topic_desc;
        $topic_author = (int)$topic_author;

        // Свързване на параметрите
        $stmt->bindParam(":parent", $parent);
        $stmt->bindParam(":topic_name", $topic_name);
        $stmt->bindParam(":topic_desc", $topic_desc);
        $stmt->bindParam(":topic_author", $topic_author);

        // Изпълнение на заявката
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

	// В models/Topics.php
	public function deleteTopic($topic_id) {
		// Изтриване на коментарите, свързани с темата
		$query = "DELETE FROM comments WHERE topic_id = :topic_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);
		$stmt->execute();

		// Изтриване на самата тема
		$query = "DELETE FROM topics WHERE topic_id = :topic_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);

		return $stmt->execute();
	}

    // Метод за преместване на тема в друга категория
    public function moveTopic($topic_id, $new_parent) {
        $query = "UPDATE " . $this->table_name . " SET parent = :new_parent WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);

        // Почистване на входните данни
        $topic_id = (int)$topic_id;
        $new_parent = (int)$new_parent;

        // Свързване на параметрите
        $stmt->bindParam(":new_parent", $new_parent);
        $stmt->bindParam(":topic_id", $topic_id);

        // Изпълнение на заявката
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

		// Метод за извличане на всички теми в дадена категория
	public function getTopicsByCategory($parent) {
		$query = "SELECT t.topic_id, t.topic_name, t.topic_desc, u.username AS author_name 
				  FROM " . $this->table_name . " t
				  LEFT JOIN users u ON t.topic_author = u.user_id
				  WHERE t.parent = :parent 
				  ORDER BY t.topic_id DESC";
		$stmt = $this->conn->prepare($query);

		$parent = (int)$parent;
		$stmt->bindParam(":parent", $parent);
		$stmt->execute();

		return $stmt;
	}	

	// Метод за извличане на тема по ID без Parsedown ( за едит топик )
	public function getTopicById($topic_id) {
		$query = "SELECT * FROM " . $this->table_name . " WHERE topic_id = :topic_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);
		$stmt->execute();
		
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}


	// Метод за актуализиране на тема
	public function updateTopic($topic_id, $topic_name, $topic_desc) {
		$query = "UPDATE " . $this->table_name . " SET topic_name = :topic_name, topic_desc = :topic_desc WHERE topic_id = :topic_id";
		$stmt = $this->conn->prepare($query);

		// Почистване на topic_name, но не и на topic_desc
		$topic_name = htmlspecialchars(strip_tags($topic_name));
		$topic_id = (int)$topic_id;

		// Свързване на параметрите
		$stmt->bindParam(":topic_name", $topic_name);
		$stmt->bindParam(":topic_desc", $topic_desc); // Без htmlspecialchars
		$stmt->bindParam(":topic_id", $topic_id);

		// Изпълнение на заявката
		if ($stmt->execute()) {
			return true;
		}
		return false;
	}
	
	public function getLastTopics($limit = 5) {
		$query = "SELECT t.topic_id, t.topic_name, t.topic_desc, u.username AS author_name, c.cat_name AS category_name, c.cat_id AS category_id
				  FROM " . $this->table_name . " t
				  LEFT JOIN users u ON t.topic_author = u.user_id
				  LEFT JOIN categories c ON t.parent = c.cat_id
				  ORDER BY t.topic_id DESC
				  LIMIT :limit";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt;
	}
	public function getTopicsByCategoryPaginated($parent, $limit, $offset) {
		$query = "SELECT t.topic_id, t.topic_name, t.topic_desc, u.username AS author_name 
				  FROM " . $this->table_name . " t
				  LEFT JOIN users u ON t.topic_author = u.user_id
				  WHERE t.parent = :parent 
				  ORDER BY t.topic_id DESC
				  LIMIT :limit OFFSET :offset";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":parent", $parent, PDO::PARAM_INT);
		$stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
		$stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt;
	}

	public function getTotalTopicsByCategory($parent) {
		$query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE parent = :parent";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":parent", $parent, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}
