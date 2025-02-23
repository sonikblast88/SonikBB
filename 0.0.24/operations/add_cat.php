<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
    $cat_name = trim(filter_input(INPUT_POST, 'cat_name')); // Removed addslashes()
    $def_icon = trim(filter_input(INPUT_POST, 'def_icon')); // Removed addslashes()
    $cat_desc = trim(filter_input(INPUT_POST, 'cat_desc')); // Removed addslashes()

    // Using prepared statement to retrieve the position
    $sql = "SELECT COUNT(*) as cnt FROM categories WHERE cat_id > 0";
    $stmt = run_q($sql);
    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $row['cnt'];
    } else {
        die("Error retrieving position."); // Error handling
    }

    if ($form_submit == 1) {
        // Using prepared statement to add a category
        $sql = "INSERT INTO categories (position, cat_name, def_icon, cat_desc) VALUES (:position, :cat_name, :def_icon, :cat_desc)";
        $params = [
            ":position" => $position,
            ":cat_name" => $cat_name,
            ":def_icon" => $def_icon,
            ":cat_desc" => $cat_desc
        ];
        $result = run_q($sql, $params);

        if ($result) {
            redirect('../index.php');
        } else {
            echo "Error adding category."; // Error handling
        }
    }
} else {
    redirect('../index.php'); // Redirect users who do not have rights
    exit;
}

include '../template/header.php';
?>

<form action="add_cat.php" method="POST">
    <input type="text" name="cat_name" value="" size="98" required><br />
    <input type="text" name="def_icon" value="images/forum.png" size="98"><br />
    <textarea name="cat_desc" id="" rows="20" cols="102"></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="submit" value="Add Category">
    <a href="../index.php"><input type="button" value="Cancel" /></a>
</form>

<?php
include '../template/footer.php';
?>