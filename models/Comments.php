<?php
// models/Comments.php
class Comments {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
        // Parsedown removed – Markdown processing is not performed here anymore.
    }

    // Method to retrieve comments for a given topic
    public function getCommentsByTopicId($topic_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE topic_id = :topic_id ORDER BY comment_id ASC";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $topic_id = (int)$topic_id;

        // Bind parameters
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Fetch results
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Removed Markdown rendering via Parsedown
        return $comments;
    }

    // Method to add a new comment
    public function addComment($topic_id, $comment, $comment_author) {
        $query = "INSERT INTO " . $this->table_name . " (topic_id, comment, comment_author, date_added_comment) VALUES (:topic_id, :comment, :comment_author, now())";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $topic_id = (int)$topic_id;
        // $comment is passed as is
        $comment_author = (int)$comment_author;

        // Bind parameters
        $stmt->bindParam(":topic_id", $topic_id, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":comment_author", $comment_author, PDO::PARAM_INT);

        // Execute the query and return the result
        return $stmt->execute();
    }

    // Method to update an existing comment
    public function updateComment($comment_id, $comment) {
        $query = "UPDATE " . $this->table_name . " SET comment = :comment WHERE comment_id = :comment_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $comment_id = (int)$comment_id;
        // $comment is passed as is

        // Bind parameters
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":comment_id", $comment_id, PDO::PARAM_INT);

        // Execute the query and return the result
        return $stmt->execute();
    }

    // Method to delete a comment by its ID
    public function deleteComment($comment_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE comment_id = :comment_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $comment_id = (int)$comment_id;

        // Bind parameter
        $stmt->bindParam(":comment_id", $comment_id, PDO::PARAM_INT);

        // Execute the query and return the result
        return $stmt->execute();
    }

    // Method to retrieve a comment by its ID
    public function getCommentById($comment_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE comment_id = :comment_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $comment_id = (int)$comment_id;

        // Bind parameter
        $stmt->bindParam(":comment_id", $comment_id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
