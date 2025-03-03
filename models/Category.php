<?php
// models/Category.php
class Category {
    protected $conn;
    private $table_name = "categories";

    private $cat_id;
    private $position;
    private $cat_name;
    private $cat_desc;
    private $def_icon;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get the current position of a category
    private function getCurrentPosition($cat_id) {
        $query = "SELECT position FROM " . $this->table_name . " WHERE cat_id = :cat_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cat_id", $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['position'];
    }

    // Get the adjacent category based on the current position and direction (up or down)
    private function getAdjacentCategory($current_position, $direction) {
        $order = ($direction === 'up') ? 'DESC' : 'ASC';
        $operator = ($direction === 'up') ? '<' : '>';
        $query = "SELECT cat_id, position FROM " . $this->table_name . " WHERE position " . $operator . " :current_position ORDER BY position " . $order . " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":current_position", $current_position, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Swap the position of the category with its adjacent one based on the given direction
    public function moveCategory($cat_id, $direction) {
        $this->conn->beginTransaction();
        try {
            $current_position = $this->getCurrentPosition($cat_id);
            $adjacent_category = $this->getAdjacentCategory($current_position, $direction);

            if ($adjacent_category) {
                $query = "UPDATE " . $this->table_name . " SET position = :new_position WHERE cat_id = :cat_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":new_position", $adjacent_category['position'], PDO::PARAM_INT);
                $stmt->bindParam(":cat_id", $cat_id, PDO::PARAM_INT);
                $stmt->execute();

                $stmt->bindParam(":new_position", $current_position, PDO::PARAM_INT);
                $stmt->bindParam(":cat_id", $adjacent_category['cat_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Move category up in the order
    public function moveUp($cat_id) {
        $this->moveCategory($cat_id, 'up');
    }

    // Move category down in the order
    public function moveDown($cat_id) {
        $this->moveCategory($cat_id, 'down');
    }

    // Create a new category with a new position (set as max position + 1)
    public function createCategory($cat_name, $cat_desc, $def_icon) {
        $position = $this->getMaxPosition() + 1;
        $query = "INSERT INTO " . $this->table_name . " (cat_name, cat_desc, def_icon, position) VALUES (:cat_name, :cat_desc, :def_icon, :position)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cat_name", $cat_name);
        $stmt->bindParam(":cat_desc", $cat_desc);
        $stmt->bindParam(":def_icon", $def_icon);
        $stmt->bindParam(":position", $position);
        return $stmt->execute();
    }

    // Get the maximum position value among all categories
    public function getMaxPosition() {
        $query = "SELECT MAX(position) FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Update an existing category
    public function updateCategory($cat_id, $cat_name, $cat_desc, $def_icon) {
        $query = "UPDATE " . $this->table_name . " SET cat_name = :cat_name, cat_desc = :cat_desc, def_icon = :def_icon WHERE cat_id = :cat_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $cat_name = htmlspecialchars(strip_tags($cat_name));
        $cat_desc = htmlspecialchars(strip_tags($cat_desc));
        $def_icon = htmlspecialchars(strip_tags($def_icon));
        $cat_id = (int)$cat_id;

        // Bind parameters
        $stmt->bindParam(":cat_name", $cat_name);
        $stmt->bindParam(":cat_desc", $cat_desc);
        $stmt->bindParam(":def_icon", $def_icon);
        $stmt->bindParam(":cat_id", $cat_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Handle a delete request for a category
    public function handleDeleteRequest($cat_id) {
        return $this->delete($cat_id);
    }

    // Handle a move request (up or down) for a category
    public function handleMoveRequest($action, $cat_id) {
        if ($action === 'move_up') {
            return $this->moveUp($cat_id);
        } elseif ($action === 'move_down') {
            return $this->moveDown($cat_id);
        }
        return false;
    }

    // Retrieve a category by its ID
    public function getCategoryById($cat_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE cat_id = :cat_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cat_id", $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a category by its ID
    public function delete($cat_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE cat_id = :cat_id";
        $stmt = $this->conn->prepare($query);

        // Clean input data
        $cat_id = (int)$cat_id;

        // Bind parameter
        $stmt->bindParam(":cat_id", $cat_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Retrieve all categories (only ID and name)
    public function getAllCategories() {
        $query = "SELECT cat_id, cat_name FROM " . $this->table_name . " ORDER BY cat_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // List all categories with details and additional counts
    public function listCategories() {
        $query = "SELECT cat_id, cat_name, cat_desc, def_icon, position FROM " . $this->table_name . " ORDER BY position ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $categories = [];

        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $row->topic_count = $this->countTopics($row->cat_id);
            $row->comment_count = $this->countComments($row->cat_id);
            $categories[] = $row;
        }
        return $categories;
    }

    // Count the number of topics for a given category
    public function countTopics($cat_id) {
        $sql_topics = "SELECT COUNT(*) FROM topics WHERE parent = :cat_id";
        $stmt = $this->conn->prepare($sql_topics);
        $stmt->bindParam(":cat_id", $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Count the number of comments for a given category (joining topics and comments)
    public function countComments($cat_id) {
        $sql_comments = "SELECT COUNT(*) FROM topics AS t JOIN comments AS c ON t.topic_id = c.topic_id WHERE t.parent = :cat_id";
        $stmt = $this->conn->prepare($sql_comments);
        $stmt->bindParam(":cat_id", $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
?>
