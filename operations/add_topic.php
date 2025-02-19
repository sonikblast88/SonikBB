<?php
require_once '../config.php';
include '../functions.php';


if (isset($_SESSION['is_loged']) && $_SESSION['user_info']['type'] == 2) { // Проверяваме и типа на потребителя (администратор)
    $cat_id = (int) filter_input(INPUT_GET, 'cat_id');
    $post_cat_id = (int) filter_input(INPUT_POST, 'post_cat_id');
    $form_submit = (int) filter_input(INPUT_POST, 'form_submit');
    $topic_name = trim(filter_input(INPUT_POST, 'topic_name')); // Не използваме addslashes() с prepared statements
    $topic_desc = trim(filter_input(INPUT_POST, 'topic_desc')); // Не използваме addslashes() с prepared statements

    if ($form_submit == 1) {
        // Използваме prepared statement
        $sql = "INSERT INTO topics (parent, topic_name, topic_desc, topic_author) VALUES (:post_cat_id, :topic_name, :topic_desc, :user_id)";
        $params = [
            ":post_cat_id" => $post_cat_id,
            ":topic_name" => $topic_name,
            ":topic_desc" => $topic_desc,
            ":user_id" => $_SESSION['user_info']['user_id']
        ];
        $result = run_q($sql, $params);

        if ($result) { // Проверяваме дали заявката е успешна
            redirect('../topics.php?cat_id=' . $post_cat_id);
        } else {
            echo "Грешка при добавяне на тема."; // Обработка на грешката
        }
    }
} else {
    // Ако не е логнат администратор, пренасочваме го към index.php или друга страница
    redirect('../index.php'); // Или друга подходяща страница
    exit; // Важно е да използваме exit след redirect, за да спрем изпълнението на скрипта
}

include '../template/header.php';
?>

<form action="add_topic.php?cat_id=<?php echo $cat_id; ?>" method="POST">  <input type="text" name="topic_name" value="" size="98" placeholder="Име на темата" required><br />
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

    <textarea name="topic_desc" id="topic_desc" placeholder="Описание на темата..." rows="20" cols="102" required></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="hidden" name="post_cat_id" value="<?php echo $cat_id; ?>">  <input type="submit" value="Добави тема">
    <a href="../index.php"><input type="button" value="Отказ" /></a>

    <div id="fileUploadModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; padding:20px; border:1px solid gray;">
        <input type="file" id="imageUpload" name="imageUpload">
        <button type="button" onclick="uploadImage()">Upload</button>
        <button type="button" onclick="closeFileUpload()">Cancel</button>
    </div>
</form>

<script>
    // ... (JavaScript кодът остава без промяна)
</script>

<?php
include '../template/footer.php';
?>