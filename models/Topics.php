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

    // Modified method to create a new topic and return its ID on success
    public function createTopic($parent, $topic_name, $topic_desc, $topic_author) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (parent, topic_name, topic_desc, topic_author, date_added_topic) 
                  VALUES (:parent, :topic_name, :topic_desc, :topic_author, NOW())";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $parent = (int)$parent;
        $topic_name = htmlspecialchars(strip_tags($topic_name));
        // Allow HTML/Markdown in topic_desc so no cleaning is performed here.
        $topic_author = (int)$topic_author;

        // Bind parameters
        $stmt->bindParam(":parent", $parent, PDO::PARAM_INT);
        $stmt->bindParam(":topic_name", $topic_name);
        $stmt->bindParam(":topic_desc", $topic_desc);
        $stmt->bindParam(":topic_author", $topic_author, PDO::PARAM_INT);

        // Execute the query and return the last inserted ID on success
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }

    // Method to delete a topic along with its associated comments
    public function deleteTopic($topic_id) {
        // Delete comments related to the topic
        $query = "DELETE FROM comments WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);
        $stmt->execute();

        // Delete the topic itself
        $query = "DELETE FROM topics WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Method to move a topic to another category
    public function moveTopic($topic_id, $new_parent) {
        $query = "UPDATE " . $this->table_name . " SET parent = :new_parent WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $topic_id = (int)$topic_id;
        $new_parent = (int)$new_parent;

        // Bind parameters
        $stmt->bindParam(":new_parent", $new_parent, PDO::PARAM_INT);
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);

        // Execute the query
        return $stmt->execute();
    }

    // Method to retrieve all topics for a given category
    public function getTopicsByCategory($parent) {
        $query = "SELECT t.topic_id, t.topic_name, t.topic_desc, u.username AS author_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.topic_author = u.user_id
                  WHERE t.parent = :parent 
                  ORDER BY t.topic_id DESC";
        $stmt = $this->conn->prepare($query);

        $parent = (int)$parent;
        $stmt->bindParam(":parent", $parent, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Method to retrieve a topic by its ID (used for editing)
    public function getTopicById($topic_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Method to update an existing topic
    public function updateTopic($topic_id, $topic_name, $topic_desc) {
        $query = "UPDATE " . $this->table_name . " SET topic_name = :topic_name, topic_desc = :topic_desc WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);

        // Clean the topic name (but not the topic description to allow formatting)
        $topic_name = htmlspecialchars(strip_tags($topic_name));
        $topic_id = (int)$topic_id;

        // Bind parameters
        $stmt->bindParam(":topic_name", $topic_name);
        $stmt->bindParam(":topic_desc", $topic_desc);
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);

        // Execute the query
        return $stmt->execute();
    }

    // Method to retrieve the latest topics with a limit
	public function getLastTopics($limit = 5) {
		$query = "SELECT t.topic_id, t.topic_name, t.topic_desc, t.date_added_topic, 
						 u.username AS author_name, c.cat_name AS category_name, c.cat_id AS category_id
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

    // Method to retrieve topics for a given category with pagination
    public function getTopicsByCategoryPaginated($parent, $limit, $offset) {
        $query = "SELECT t.topic_id, t.topic_name, t.topic_desc, u.username AS author_name, date_added_topic 
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

    // Method to get the total number of topics for a given category
    public function getTotalTopicsByCategory($parent) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE parent = :parent";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":parent", $parent, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Method to get the number of comments for a given topic
    public function getCommentCountByTopicId($topic_id) {
        $query = "SELECT COUNT(*) FROM comments WHERE topic_id = :topic_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

	//method to calculate number of times a page has been viewed
    public function getVisitCountByTopicId($topic_id) {
        $query = "SELECT COUNT(*) FROM visitors WHERE page_visited LIKE :page_visited";
        $stmt = $this->conn->prepare($query);
        $page_visited = "%topic.php?topic_id=" . $topic_id . "%"; // Образец за URL на страницата
        $stmt->bindParam(':page_visited', $page_visited);
        $stmt->execute();

        return $stmt->fetchColumn(); // Връща броя на посещенията
    }

	// Method to generate keywords from topic description
	public function generateKeywords($topic_id, $limit = 10) {
		$query = "SELECT topic_desc FROM " . $this->table_name . " WHERE topic_id = :topic_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);
		$stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$result) return '';

		$text = strtolower(strip_tags($result['topic_desc']));
		$text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // Remove punctuation
		$words = explode(' ', $text);

		// Common stop words (you can add more)
		$stopWords = ['and', 'to', 'in', 'of', 'with', 'for', 'that', 'as', 'but', 'or', 'from', 'so', 'by'];

		// Remove empty elements and unnecessary spaces
		$wordFrequency = [];
		foreach ($words as $word) {
			$word = trim($word);
			if (mb_strlen($word) > 3 && !in_array($word, $stopWords) && !empty($word)) {
				$wordFrequency[$word] = isset($wordFrequency[$word]) ? $wordFrequency[$word] + 1 : 1;
			}
		}

		// Sort by frequency (most common words first)
		arsort($wordFrequency);
		$keywordsArray = array_keys(array_slice($wordFrequency, 0, $limit));

		// **Remove extra spaces and new lines**
		$keywords = implode(', ', array_filter($keywordsArray));
		$keywords = preg_replace('/\s+/', ' ', $keywords); // Remove extra spaces
		return trim($keywords);
	}

}
?>
