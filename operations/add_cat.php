<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
    $cat_name = trim(filter_input(INPUT_POST, 'cat_name')); // Премахнато addslashes()
    $def_icon = trim(filter_input(INPUT_POST, 'def_icon')); // Премахнато addslashes()
    $cat_desc = trim(filter_input(INPUT_POST, 'cat_desc')); // Премахнато addslashes()

    // Използваме prepared statement за извличане на позицията
    $sql = "SELECT COUNT(*) as cnt FROM categories WHERE cat_id > 0";
    $stmt = run_q($sql);
    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $row['cnt'];
    } else {
        die("Грешка при извличане на позицията."); // Обработка на грешката
    }

    if ($form_submit == 1) {
        // Използваме prepared statement за добавяне на категория
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
            echo "Грешка при добавяне на категория."; // Обработка на грешката
        }
    }
} else {
    redirect('../index.php'); // Пренасочваме потребители, които нямат права
    exit;
}

include '../template/header.php';
?>

<form action="add_cat.php" method="POST">
    <input type="text" name="cat_name" value="" size="98" required><br />  <input type="text" name="def_icon" value="images/forum.png" size="98"><br />
    <textarea name="cat_desc" id="" rows="20" cols="102"></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="submit" value="Add Category">
    <a href="../index.php"><input type="button" value="Cancel" /></a>
</form>

<?php
include '../template/footer.php';
?>