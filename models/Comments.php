<?php
// Comments.php
class Comments {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
		$this->parsedown = new Parsedown(); // Инициализиране на Parsedown
    }

    // Метод за извличане на коментарите за дадена тема
public function getCommentsByTopicId($topic_id) {
    $query = "SELECT * FROM " . $this->table_name . " WHERE topic_id = :topic_id ORDER BY comment_id ASC";
    $stmt = $this->conn->prepare($query);

    // Почистване на входните данни
    $topic_id = (int)$topic_id;

    // Свързване на параметрите
    $stmt->bindParam(":topic_id", $topic_id);

    // Изпълнение на заявката
    $stmt->execute();

    // Вземане на резултатите
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Рендиране на Markdown за всеки коментар
    foreach ($comments as &$comment) {
        $comment['comment'] = $this->parsedown->text($comment['comment']);
    }

    return $comments;
}

    // Метод за добавяне на коментар
    public function addComment($topic_id, $comment, $comment_author) {
        $query = "INSERT INTO " . $this->table_name . " (topic_id, comment, comment_author, date_added_comment) VALUES (:topic_id, :comment, :comment_author, now())";
        $stmt = $this->conn->prepare($query);

        // Почистване на входните данни
        $topic_id = (int)$topic_id;
        $comment = $comment;
        $comment_author = (int)$comment_author;

        // Свързване на параметрите
        $stmt->bindParam(":topic_id", $topic_id);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":comment_author", $comment_author);

        // Изпълнение на заявката
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод за редактиране на коментар
    public function updateComment($comment_id, $comment) {
        $query = "UPDATE " . $this->table_name . " SET comment = :comment WHERE comment_id = :comment_id";
        $stmt = $this->conn->prepare($query);

        // Почистване на входните данни
        $comment_id = (int)$comment_id;
        $comment = $comment;

        // Свързване на параметрите
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":comment_id", $comment_id);

        // Изпълнение на заявката
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Метод за изтриване на коментар
    public function deleteComment($comment_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE comment_id = :comment_id";
        $stmt = $this->conn->prepare($query);

        // Почистване на входните данни
        $comment_id = (int)$comment_id;

        // Свързване на параметрите
        $stmt->bindParam(":comment_id", $comment_id);

        // Изпълнение на заявката
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
	
	// Comments.php
	public function getCommentById($comment_id) {
		$query = "SELECT * FROM " . $this->table_name . " WHERE comment_id = :comment_id";
		$stmt = $this->conn->prepare($query);

		// Почистване на входните данни
		$comment_id = (int)$comment_id;

		// Свързване на параметрите
		$stmt->bindParam(":comment_id", $comment_id);

		// Изпълнение на заявката
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}
