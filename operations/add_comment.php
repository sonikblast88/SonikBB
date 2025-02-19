<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) { // Проверяваме и типа на потребителя (администратор)
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');
    $topic_id = (int)filter_input(INPUT_GET, 'topic_id');
    $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
    $comment = trim(filter_input(INPUT_POST, 'comment')); // Не използваме addslashes() с prepared statements
    $post_topic_id = (int)filter_input(INPUT_POST, 'post_topic_id');
    $post_cat_id = (int)filter_input(INPUT_POST, 'post_cat_id');

    if ($form_submit == 1) {
        // Използваме prepared statement
        $sql = "INSERT INTO comments (topic_id, comment, comment_author) VALUES (:post_topic_id, :comment, :user_id)";
        $params = [
            ":post_topic_id" => $post_topic_id,
            ":comment" => $comment,
            ":user_id" => $_SESSION['user_info']['user_id']
        ];
        $result = run_q($sql, $params);

        if ($result) { // Проверяваме дали заявката е успешна
            redirect('../topic.php?topic_id=' . $post_topic_id . '&cat_id=' . $post_cat_id);
        } else {
            echo "Грешка при добавяне на коментар."; // Обработка на грешката
        }
    }
} else {
    // Ако не е логнат администратор, пренасочваме го към index.php или друга страница
    redirect('../index.php'); // Или друга подходяща страница
    exit; // Важно е да използваме exit след redirect, за да спрем изпълнението на скрипта
}

include '../template/header.php';
?>

<form action="add_comment.php?topic_id=<?php echo $topic_id; ?>&cat_id=<?php echo $cat_id; ?>" method="POST">
    <div class="toolbar">
        <button type="button" onclick="formatText('bold')"><b>B</b></button>
        <button type="button" onclick="formatText('italic')"><i>I</i></button>
        <button type="button" onclick="insertImage()">Image</button>
        <button type="button" onclick="formatText('link')">Link</button>
        <button type="button" onclick="formatText('code')">Code</button>
        <button type="button" onclick="formatText('heading')">Heading</button>
        <button type="button" onclick="formatText('list')">List</button>
        <button type="button" onclick="formatText('quote')">Quote</button>
        <button type="button" onclick="openFileUpload()">Upload Image</button>
    </div>

    <textarea name="comment" id="comment" rows="20" cols="102"></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="hidden" name="post_topic_id" value="<?php echo $topic_id; ?>">
    <input type="hidden" name="post_cat_id" value="<?php echo $cat_id; ?>">
    <input type="submit" value="Add Comment">
    <a href="../index.php"><input type="button" value="Cancel" /></a>
</form>

<div id="fileUploadModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; padding:20px; border:1px solid gray;">
    <input type="file" id="imageUpload" name="imageUpload">
    <button type="button" onclick="uploadImage()">Upload</button>
    <button type="button" onclick="closeFileUpload()">Cancel</button>
</div>

<script>
    // ... (JavaScript кодът остава без промяна)
</script>

<?php
include '../template/footer.php';
?>