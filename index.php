<?php
// index.php
session_start();

include_once 'core/autoload.php';
include_once 'models/Category.php';
include_once 'models/Users.php';
include_once 'models/Topics.php';
include_once 'template/header.php';

define("WEBSITE", "https://webleit.eu/dev5/");

$database = new Database();
$db = $database->connect();

$categoryModel = new Category($db);
$topicsModel = new Topics($db);

$showAddCategoryForm = false;
$showEditCategoryForm = false;
$editCategory = null;

// Обработка на заявки за изтриване
if (isset($_GET['delete'])) {
    $cat_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($cat_id !== false && $categoryModel->handleDeleteRequest($cat_id)) {
        header("Location: index.php");
        exit;
    }
}

// Обработка на заявки за промяна на позицията
if (isset($_GET['action'], $_GET['cat_id'])) {
    $cat_id = filter_var($_GET['cat_id'], FILTER_VALIDATE_INT);

    if ($cat_id !== false && $categoryModel->handleMoveRequest($_GET['action'], $cat_id)) {
        header("Location: index.php");
        exit;
    }
}

// Проверка за администратор
$is_admin = isAdmin();

// Добавяне на категория
if ($is_admin && isset($_GET['add_category'])) {
    $showAddCategoryForm = true;
}

// Редактиране на категория
if ($is_admin && isset($_GET['edit_category'])) {
    $cat_id = filter_var($_GET['edit_category'], FILTER_VALIDATE_INT);
    if ($cat_id !== false) {
        $showEditCategoryForm = true;
        $editCategory = $categoryModel->getCategoryById($cat_id);
    }
}

// Обработка на формата за добавяне на категория
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category_form'])) {
    $cat_name = htmlspecialchars(strip_tags($_POST['cat_name']));
    $cat_desc = htmlspecialchars(strip_tags($_POST['cat_desc']));
    $def_icon = htmlspecialchars(strip_tags($_POST['def_icon']));

    if ($categoryModel->createCategory($cat_name, $cat_desc, $def_icon)) {
        header("Location: index.php");
        exit();
    }
}

// Обработка на формата за редактиране на категория
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

// Извличане на категории
$categories = $categoryModel->listCategories();
//var_dump($categories); // Добавете тази линия
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
            <input type="text" name="def_icon" placeholder="Default Icon" required>
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

<div id="aside">
    <div id="profile">
        <?php
        $type = '<div id="profile-info"><b>Type:</b> USER</div>';
        $stats = '';

        if (isset($_SESSION['is_loged'])) {
            if ($_SESSION['type'] == 2) {
                $type = '<div id="profile-info"><b>Type:</b> Administrator</div>';
                $stats = '<div id="profile-info"><b>» <a href="/stats.php">Forums Stats</a></b></div>';
            }

            echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
            echo '<img src="/' . htmlspecialchars($_SESSION['avatar']) . '" alt="" id="profile-image" />';
            echo '<div id="profile-info"><b>Name:</b> ' . htmlspecialchars($_SESSION['username']) . '</div>';
            echo $type;
            echo $stats;
            echo '<div id="profile-info"><b>» <a href="profile.php?profile_id=' . (int)$_SESSION['user_id'] . '">Edit Profile</a></b></div>';
            echo '<div id="profile-info"><b>» <a href="logout.php">Log Out</a></b></div>';
            echo '<div id="profile-info">' . htmlspecialchars($_SESSION['signature']) . '</div>';
        } else {
            echo '<div id="last-topics-topic-header">» P R O F I L E</div>';
            echo '<img src="template/images/avatar-default.avif" alt="" id="profile-image" />';
            echo '<div id="profile-info">You are not currently logged in. Please <b><a href="login.php">log in</a></b> or <a href="register.php"><b>register</b></a></div>';
        }
        ?>
    </div>

    <br />
	<div id="last-topics">
		<div id="last-topics-topic-header">» L A S T - T O P I C S</div>
		<?php
		$lastTopics = $topicsModel->getLastTopics();
		while ($row = $lastTopics->fetch(PDO::FETCH_ASSOC)): ?>
			<div id="last-topics-topic">
				» <a href="topic.php?topic_id=<?= $row['topic_id'] ?>"><?= $row['topic_name'] ?></a> (<?= $row['category_name'] ?>)
			</div>
		<?php endwhile; ?>
	</div>
</div>

<?php include_once 'template/footer.php'; ?>