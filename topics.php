<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';
include_once 'models/Category.php';
include_once 'models/Users.php';
include_once 'template/header.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);
$categoryModel = new Category($db);

// Check if user is admin
$is_admin = isAdmin();

// Check if user is either admin or a regular user
$isUserOrAdmin = isUserOrAdmin();

// Process requests for deleting and moving topics
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_topic'])) {
        $topic_id = $_POST['topic_id'];
        $parent = $_POST['parent'];
        if ($topicsModel->deleteTopic($topic_id)) {
            header("Location: topics.php?cat_id=" . $parent);
            exit();
        }
    } elseif (isset($_POST['move_topic'])) {
        $topic_id = $_POST['topic_id'];
        $new_parent = $_POST['new_parent'];
        if ($topicsModel->moveTopic($topic_id, $new_parent)) {
            header("Location: topics.php?cat_id=" . $new_parent);
            exit();
        }
    }
}

// Retrieve category ID from URL
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15; // Number of topics per page
$offset = ($page - 1) * $limit;

// Retrieve topics for the category with pagination
$topics = $topicsModel->getTopicsByCategoryPaginated($cat_id, $limit, $offset);

// Get total number of topics in the category
$totalTopics = $topicsModel->getTotalTopicsByCategory($cat_id);
$totalPages = ceil($totalTopics / $limit);

// Retrieve all categories and store them in an array
$categories = $categoryModel->getAllCategories();
$categoriesList = [];
while ($category = $categories->fetch(PDO::FETCH_ASSOC)) {
    $categoriesList[] = $category;
}
?>

<div id="content">
    <?php if ($isUserOrAdmin): ?>
        <center><a href="add_topic.php?cat_id=<?= $cat_id ?>"><img src="template/images/add-topic.png" alt="Add Topic" /></a></center>
    <?php endif; ?>
    <br>
    <div id="topic">
        <?php if ($topics->rowCount() == 0): ?>
            <p>There are no articles at the moment.</p>
        <?php else: ?>
            <?php while ($row = $topics->fetch(PDO::FETCH_ASSOC)): ?>
                <div id="list-topics">
                    » <a href="topic.php?topic_id=<?= $row['topic_id'] ?>"><?= htmlspecialchars($row['topic_name']) ?></a> 
                    <hr style="border: none; border-bottom: dashed 1px #000000;">
                    - <small>Added by: <?= htmlspecialchars($row['author_name']) ?> on: <?= htmlspecialchars($row['date_added_topic']) ?> with: ( <?= $topicsModel->getCommentCountByTopicId($row['topic_id']) ?> ) comments</small>
                    
                    <div style="float:right;">
                        <?php if ($is_admin): ?>
                            <form method="GET" action="edit_topic.php" style="display:inline;">
                                <input type="hidden" name="topic_id" value="<?= $row['topic_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>

                            <form method="POST" action="topics.php" style="display:inline;">
                                <input type="hidden" name="topic_id" value="<?= $row['topic_id'] ?>">
                                <input type="hidden" name="parent" value="<?= $cat_id ?>">
                                <button type="submit" name="delete_topic" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>

                            <form method="POST" action="topics.php" style="display:inline;">
                                <input type="hidden" name="topic_id" value="<?= $row['topic_id'] ?>">
                                <select name="new_parent" required>
                                    <?php foreach ($categoriesList as $category): ?>
                                        <option value="<?= $category['cat_id'] ?>"><?= $category['cat_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="move_topic">Move</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <br>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="topics.php?cat_id=<?= $cat_id ?>&page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="topics.php?cat_id=<?= $cat_id ?>&page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="topics.php?cat_id=<?= $cat_id ?>&page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <br>
    <?php if ($isUserOrAdmin): ?>
        <center><a href="add_topic.php?cat_id=<?= $cat_id ?>"><img src="template/images/add-topic.png" alt="Add Topic" /></a></center>
    <?php endif; ?>
</div>

<?php
include_once('aside.php');
include_once 'template/footer.php';
?>
