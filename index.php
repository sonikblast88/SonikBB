<?php
// index.php
session_start();

// Check if config.php is empty
if (filesize('core/config.php') === 0) {
    header('Location: install/index.php');
    exit;
}

require_once 'core/autoload.php';
require_once 'models/Category.php';
require_once 'models/Users.php';
require_once 'models/Topics.php';
require_once 'template/header.php';

$database = new Database();
$db = $database->connect();

// Check for successful database connection
if (!$db) {
    echo "Error connecting to the database. Please try again later.";
    exit; // Stop execution
}

$categoryModel = new Category($db);
$topicsModel = new Topics($db);

$showAddCategoryForm = false;
$showEditCategoryForm = false;
$editCategory = null;

// Handle delete request
if (isset($_GET['delete'])) {
    $cat_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($cat_id !== false && $categoryModel->handleDeleteRequest($cat_id)) {
        header("Location: index.php");
        exit;
    }
}

// Handle position change request
if (isset($_GET['action'], $_GET['cat_id'])) {
    $cat_id = filter_var($_GET['cat_id'], FILTER_VALIDATE_INT);
    if ($cat_id !== false && $categoryModel->handleMoveRequest($_GET['action'], $cat_id)) {
        header("Location: index.php");
        exit;
    }
}

// Check if user is admin
$is_admin = isAdmin();

// Display add category form if admin and requested
if ($is_admin && isset($_GET['add_category'])) {
    $showAddCategoryForm = true;
}

// Display edit category form if admin and requested
if ($is_admin && isset($_GET['edit_category'])) {
    $cat_id = filter_var($_GET['edit_category'], FILTER_VALIDATE_INT);
    if ($cat_id !== false) {
        $showEditCategoryForm = true;
        $editCategory = $categoryModel->getCategoryById($cat_id);
    }
}

// Process add category form submission
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category_form'])) {
    $cat_name = htmlspecialchars(strip_tags($_POST['cat_name']));
    $cat_desc = htmlspecialchars(strip_tags($_POST['cat_desc']));
    $def_icon = htmlspecialchars(strip_tags($_POST['def_icon']));

    if ($categoryModel->createCategory($cat_name, $cat_desc, $def_icon)) {
        header("Location: index.php");
        exit();
    }
}

// Process edit category form submission
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category_form'])) {
    $cat_id = (int)$_POST['cat_id'];
    $cat_name = htmlspecialchars(strip_tags($_POST['cat_name']));
    $cat_desc = htmlspecialchars(strip_tags($_POST['cat_desc']));
    $def_icon = htmlspecialchars(strip_tags($_POST['def_icon']));

    if ($categoryModel->updateCategory($cat_id, $cat_name, $cat_desc, $def_icon)) {
        header("Location: index.php");
        exit();
    }
}

// Retrieve categories
$categories = $categoryModel->listCategories();
// var_dump($categories); // Uncomment this line for debugging
?>

<div id="content">
    <?php foreach ($categories as $category): ?>
        <div id="forum">
            <div id="forum-picture">
                <img src="template/<?= htmlspecialchars($category->def_icon) ?>" alt="" id="forum-picture" />
            </div>
            <div id="forum-title">
                <b>» <a href="topics.php?cat_id=<?= htmlspecialchars($category->cat_id) ?>"><?= htmlspecialchars($category->cat_name) ?></a></b>
            </div>
            <div id="forum-operations">
                <?php if ($is_admin): ?>
                    <?php
                    $edit = '<a href="index.php?edit_category=' . htmlspecialchars($category->cat_id) . '">[ Edit ]</a> ';
                    $delete = '<a href="index.php?delete=' . htmlspecialchars($category->cat_id) . '" onclick="return confirm(\'Are you sure?\')">[ Delete ]</a> ';
                    $moveup = '<a href="index.php?action=move_up&cat_id=' . htmlspecialchars($category->cat_id) . '">[ ↑ ]</a> ';
                    $movedown = '<a href="index.php?action=move_down&cat_id=' . htmlspecialchars($category->cat_id) . '">[ ↓ ]</a>';
                    echo $edit . $delete . $moveup . $movedown;
                    ?>
                <?php endif; ?>
                <div style="float: right;">
                    Total Topics (<b><?= htmlspecialchars($category->topic_count) ?></b>)
                    Total Comments (<b><?= htmlspecialchars($category->comment_count) ?></b>)
                </div>
            </div>
            <div id="forum-desc"><?= htmlspecialchars($category->cat_desc) ?></div>
        </div>
        <br />
    <?php endforeach; ?>

    <?php if ($is_admin): ?>
        <center><a href="index.php?add_category=true"><img src="template/images/add-cat.png" alt="Add Category" /></a></center>
    <?php endif; ?>

    <?php if ($showAddCategoryForm): ?>
        <h2>Add Category</h2>
        <form method="POST" action="index.php">
            <input type="hidden" name="add_category_form" value="1">
            <input type="text" name="cat_name" placeholder="Category Name" required>
            <input type="text" name="cat_desc" placeholder="Category Description" required>
            <input type="text" name="def_icon" placeholder="Default Icon" value="images/forum.png" required>
            <button type="submit">Create</button>
            <a href="index.php"><button type="button">Cancel</button></a>
        </form>
    <?php endif; ?>

    <?php if ($showEditCategoryForm && $editCategory): ?>
        <h2>Edit Category</h2>
        <form method="POST" action="index.php">
            <input type="hidden" name="edit_category_form" value="1">
            <input type="hidden" name="cat_id" value="<?= htmlspecialchars($editCategory['cat_id']) ?>">
            <input type="text" name="cat_name" value="<?= htmlspecialchars($editCategory['cat_name']) ?>" required>
            <input type="text" name="cat_desc" value="<?= htmlspecialchars($editCategory['cat_desc']) ?>" required>
            <input type="text" name="def_icon" value="<?= htmlspecialchars($editCategory['def_icon']) ?>" required>
            <button type="submit" name="update">Update</button>
            <a href="index.php"><button type="button">Cancel</button></a>
        </form>
    <?php endif; ?>
</div>

<?php 
include_once('aside.php');
include_once 'template/footer.php';
?>