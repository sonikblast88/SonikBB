<?php
include 'functions.php';
include 'template/header.php';

echo '<div id="content">';

$sql = "SELECT cat_id, cat_name, cat_desc, def_icon FROM categories ORDER BY position";
$query = run_q($sql);

if ($query) {
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
            $delete = '<a href="operations/del_cat.php?cat_id=' . (int)$row['cat_id'] . '" onclick="return confirm(\'Are you sure you want to delete this category?\')">[ Изтрии ]</a>';
            $edit = '<a href="operations/edit_cat.php?cat_id=' . (int)$row['cat_id'] . '">[ Редактирай ]</a>';
            $up = '<a href="operations/movecat.php?cat_id=' . (int)$row['cat_id'] . '&&action=up">[ ↑ ]</a>';
            $down = '<a href="operations/movecat.php?cat_id=' . (int)$row['cat_id'] . '&&action=down">[ ↓ ]</a>';
        } else {
            $delete = NULL;
            $edit = NULL;
            $up = NULL;
            $down = NULL;
        }

        // Брой теми (с prepared statement)
        $sql_broi_temi = "SELECT COUNT(*) as broi_temi FROM topics WHERE parent = :cat_id";
        $params_broi_temi = [":cat_id" => (int)$row['cat_id']];
        $broi_temi = run_q($sql_broi_temi, $params_broi_temi);
        $broi_temi_result = $broi_temi->fetch(PDO::FETCH_ASSOC);

        // Брой коментари (с prepared statement)
        $sql_broi_komentari = "SELECT COUNT(*) as broi_komentari FROM topics as t, comments as c WHERE t.topic_id = c.topic_id AND t.parent = :cat_id";
        $params_broi_komentari = [":cat_id" => (int)$row['cat_id']];
        $broi_komentari = run_q($sql_broi_komentari, $params_broi_komentari);
        $broi_komentari_result = $broi_komentari->fetch(PDO::FETCH_ASSOC);

        echo '<div id="forum">
                <div id="forum-picture"><img src="template/' . $row['def_icon'] . '" alt="" id="forum-picture" /></div>
                <div id="forum-title"><b>» <a href="topics.php?cat_id=' . (int)$row['cat_id'] . '">' . htmlspecialchars($row['cat_name'], ENT_QUOTES) . '</a></b><hr /></div>
                <div id="forum-operations">&nbsp;' . $delete . ' ' . $edit . ' ' . $up . ' ' . $down . '<div style="float: right;">Общо Теми ( <b>' . (int)$broi_temi_result['broi_temi'] . '</b> ) Общо коментари ( <b>' . (int)$broi_komentari_result['broi_komentari'] . '</b> )</div></div>
                <div id="forum-desc">' . htmlspecialchars($row['cat_desc'], ENT_QUOTES) . '</div>
            </div><br />';
    }
}

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    echo '<center><a href="operations/add_cat.php"><img src="template/images/add-cat.png" alt="" /></a></center>';
}

echo '</div>';
include 'aside.php';
include 'template/footer.php';
?>