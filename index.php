<?php
include 'functions.php'; // Include database functions
include 'template/header.php'; // Include header template

echo '<div id="content">';

// SQL query to select categories, ordered by position
$sql = "SELECT cat_id, cat_name, cat_desc, def_icon FROM categories ORDER BY position";
$query = run_q($sql); // Execute the query using the run_q function

// Check if the query was successful
if ($query) {
    // Loop through each category retrieved from the database
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // Check if the user is logged in and is an administrator (type 2)
        if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
            // Generate delete, edit, up, and down links for category management
            $delete = '<a href="operations/del_cat.php?cat_id=' . (int)$row['cat_id'] . '" onclick="return confirm(\'Are you sure you want to delete this category?\')">[ Delete ]</a>';
            $edit = '<a href="operations/edit_cat.php?cat_id=' . (int)$row['cat_id'] . '">[ Edit ]</a>';
            $up = '<a href="operations/movecat.php?cat_id=' . (int)$row['cat_id'] . '&&action=up">[ ↑ ]</a>';
            $down = '<a href="operations/movecat.php?cat_id=' . (int)$row['cat_id'] . '&&action=down">[ ↓ ]</a>';
        } else {
            // If not an admin, set management links to null
            $delete = NULL;
            $edit = NULL;
            $up = NULL;
            $down = NULL;
        }

        // Prepared statement to count the number of topics in the current category
        $sql_broi_temi = "SELECT COUNT(*) as topic_count FROM topics WHERE parent = :cat_id";
        $params_broi_temi = [":cat_id" => (int)$row['cat_id']];
        $broi_temi = run_q($sql_broi_temi, $params_broi_temi); // Execute the topic count query
        $broi_temi_result = $broi_temi->fetch(PDO::FETCH_ASSOC); // Fetch the result

        // Prepared statement to count the number of comments in the current category
        $sql_broi_komentari = "SELECT COUNT(*) as comment_count FROM topics as t, comments as c WHERE t.topic_id = c.topic_id AND t.parent = :cat_id";
        $params_broi_komentari = [":cat_id" => (int)$row['cat_id']];
        $broi_komentari = run_q($sql_broi_komentari, $params_broi_komentari); // Execute the comment count query
        $broi_komentari_result = $broi_komentari->fetch(PDO::FETCH_ASSOC); // Fetch the result


        // Output the category information in an HTML div
        echo '<div id="forum">
            <div id="forum-picture"><img src="template/' . $row['def_icon'] . '" alt="" id="forum-picture" /></div>
            <div id="forum-title"><b>» <a href="topics.php?cat_id=' . (int)$row['cat_id'] . '">' . htmlspecialchars($row['cat_name'], ENT_QUOTES) . '</a></b></div>
            <div id="forum-operations">&nbsp;' . $delete . ' ' . $edit . ' ' . $up . ' ' . $down . '<div style="float: right;">Total Topics ( <b>' . (int)$broi_temi_result['topic_count'] . '</b> ) Total Comments ( <b>' . (int)$broi_komentari_result['comment_count'] . '</b> )</div></div>
            <div id="forum-desc">' . htmlspecialchars($row['cat_desc'], ENT_QUOTES) . '</div>
        </div><br />';
    }
}

// Display "Add Category" link if the user is logged in and is an administrator
if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) {
    echo '<center><a href="operations/add_cat.php"><img src="template/images/add-cat.png" alt="" /></a></center>';
}

echo '</div>'; // Close the content div
include 'aside.php'; // Include the aside template
include 'template/footer.php'; // Include the footer template
?>
