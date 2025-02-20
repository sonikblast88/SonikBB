<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');
    $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
    $cat_name = trim(filter_input(INPUT_POST, 'cat_name')); // Removed addslashes()
    $cat_desc = trim(filter_input(INPUT_POST, 'cat_desc')); // Removed addslashes()
    $def_icon = trim(filter_input(INPUT_POST, 'def_icon')); // Removed addslashes()
    $post_cat_id = (int)filter_input(INPUT_POST, 'post_cat_id');

    // Retrieving category data (using prepared statement)
    $sql_select = "SELECT cat_name, cat_desc, def_icon FROM categories WHERE cat_id = :cat_id";
    $params_select = [":cat_id" => $cat_id];
    $stmt = run_q($sql_select, $params_select);

    if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Checking $stmt and if there is a result

        if ($form_submit == 1) {
            // Editing the category (using prepared statement)
            $sql_update = "UPDATE categories SET cat_name = :cat_name, cat_desc = :cat_desc, def_icon = :def_icon WHERE cat_id = :post_cat_id";
            $params_update = [
                ":cat_name" => $cat_name,
                ":cat_desc" => $cat_desc,
                ":def_icon" => $def_icon,
                ":post_cat_id" => $post_cat_id
            ];
            $result = run_q($sql_update, $params_update);

            if ($result) {
                redirect('../index.php');
            } else {
                echo "Error editing the category."; // Error handling
            }
        }

    } else {
        echo "Error retrieving category data."; // Error handling
        exit; // Stopping execution if there is no result
    }

} else {
    redirect('../index.php'); // Redirecting users who do not have rights
    exit;
}

include '../template/header.php';
?>

<form action="edit_cat.php?cat_id=<?php echo $cat_id; ?>" method="POST">
    <input type="text" name="cat_name" value="<?php echo htmlspecialchars($row['cat_name'], ENT_QUOTES); ?>"><br />
    <input type="text" name="def_icon" value="<?php echo htmlspecialchars($row['def_icon'], ENT_QUOTES); ?>"><br />
    <textarea name="cat_desc" id="" rows="20"><?php echo htmlspecialchars($row['cat_desc'], ENT_QUOTES); ?></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="hidden" name="post_cat_id" value="<?php echo $cat_id ?>">
    <input type="submit" value="Edit Category">
    <a href="../index.php"><input type="button" value="Cancel" /></a>
</form>

<?php
include '../template/footer.php';
?>