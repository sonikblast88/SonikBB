<?php
declare(strict_types=1);
session_start();

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if config.php is empty (indicating an uninstalled forum)
if (file_exists('core/config.php') && filesize('core/config.php') === 0) {
    header('Location: install/index.php');
    exit;
}

// Load required files
require_once 'core/autoload.php';
require_once 'models/Category.php';
require_once 'models/Users.php';
require_once 'models/Topics.php';
require_once 'template/header.php';

// Connect to the database
try {
    $database = new Database();
    $db = $database->connect();
} catch (Exception $e) {
    die("Error: Database connection failed.");
}

$categoryModel = new Category($db);
$topicsModel = new Topics($db);
$is_admin = isAdmin();

$showAddCategoryForm = false;
$showEditCategoryForm = false;
$editCategory = null;

// ✅ Handle category movement (Admin only)
if ($is_admin && isset($_GET['action'], $_GET['cat_id']) && is_numeric($_GET['cat_id'])) {
    $cat_id = (int) $_GET['cat_id'];
    $action = $_GET['action'];

    if ($action === 'move_up') {
        $categoryModel->moveUp($cat_id);
    } elseif ($action === 'move_down') {
        $categoryModel->moveDown($cat_id);
    }

    header("Location: index.php");
    exit;
}

// ✅ Handle category editing (Admin only)
if ($is_admin && isset($_GET['edit_category']) && is_numeric($_GET['edit_category'])) {
    $cat_id = (int) $_GET['edit_category'];
    $editCategory = $categoryModel->getCategoryById($cat_id);

    if ($editCategory) {
        $showEditCategoryForm = true;
    } else {
        echo "<p style='color:red;'>Error: Category not found.</p>";
    }
}

// ✅ Handle category update (POST request)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category_form'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch!");
    }

    $cat_id = (int) $_POST['cat_id'];
    $cat_name = filter_input(INPUT_POST, 'cat_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cat_desc = filter_input(INPUT_POST, 'cat_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $def_icon = filter_input(INPUT_POST, 'def_icon', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!empty($cat_name) && !empty($cat_desc) && !empty($def_icon)) {
        if ($categoryModel->updateCategory($cat_id, $cat_name, $cat_desc, $def_icon)) {
            header("Location: index.php");
            exit();
        } else {
            echo "<p style='color:red;'>Error: Failed to update category.</p>";
        }
    } else {
        echo "<p style='color:red;'>Error: All fields are required.</p>";
    }
}

// ✅ Retrieve categories
$categories = $categoryModel->listCategories();
$max_position = $categoryModel->getMaxPosition(); // Get max category position

?>

<div id="content">
    <?php foreach ($categories as $category): ?>
        <div id="forum">
            <div id="forum-picture">
                <img src="template/<?= htmlspecialchars((string) $category->def_icon) ?>" alt="Forum Icon">
            </div>
            <div id="forum-title">
                <b>» <a href="topics.php?cat_id=<?= (int) $category->cat_id ?>"><?= htmlspecialchars((string) $category->cat_name) ?></a></b>
            </div>
            <div id="forum-operations">
                <?php if ($is_admin): ?>
                    <a href="index.php?edit_category=<?= (int) $category->cat_id ?>">[ Edit ]</a> 
                    <a href="index.php?delete=<?= (int) $category->cat_id ?>" onclick="return confirm('Are you sure?')">[ Delete ]</a> 

                    <?php if ($category->position > 1): ?>
                        <a href="index.php?action=move_up&cat_id=<?= (int) $category->cat_id ?>">[ ↑ ]</a>
                    <?php endif; ?>
                    
                    <?php if ($category->position < $max_position): ?>
                        <a href="index.php?action=move_down&cat_id=<?= (int) $category->cat_id ?>">[ ↓ ]</a>
                    <?php endif; ?>
                <?php endif; ?>
                <div style="float: right;">
                    Total Topics (<b><?= (int) $category->topic_count ?></b>)
                    Total Comments (<b><?= (int) $category->comment_count ?></b>)
                </div>
            </div>
            <div id="forum-desc"><?= htmlspecialchars((string) $category->cat_desc) ?></div>
        </div>
        <br>
    <?php endforeach; ?>

    <?php if ($showEditCategoryForm && $editCategory): ?>
        <h2>Edit Category</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="edit_category_form" value="1">
            <input type="hidden" name="cat_id" value="<?= (int) $editCategory['cat_id'] ?>">
            <input type="text" name="cat_name" value="<?= htmlspecialchars((string) $editCategory['cat_name']) ?>" required>
            <input type="text" name="cat_desc" value="<?= htmlspecialchars((string) $editCategory['cat_desc']) ?>" required>
            <input type="text" name="def_icon" value="<?= htmlspecialchars((string) $editCategory['def_icon']) ?>" required>
            <button type="submit">Update</button>
        </form>
    <?php endif; ?>
</div>

<?php 
include_once('aside.php');
include_once 'template/footer.php';
?>
