<?php
include '../functions.php';

$topic_id = (int)filter_input(INPUT_GET, 'topic_id');
$cat_id = (int)filter_input(INPUT_GET, 'cat_id');
$comment_id = (int)filter_input(INPUT_GET, 'comment_id');
$post_comment_id = (int)filter_input(INPUT_POST, 'post_comment_id');

if (isset($_SESSION['is_loged'])) {
    // Извличане на коментара (с prepared statement)
    $sql_select = "SELECT comment FROM comments WHERE comment_id = :comment_id";
    $params_select = [":comment_id" => $comment_id];
    $stmt = run_q($sql_select, $params_select);

    if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Проверяваме $stmt и дали има резултат

        if ($_SESSION['user_info']['type'] == 2 ||  // Админ винаги може да редактира
            (isset($_SESSION['user_info']['user_id']) && $_SESSION['user_info']['user_id'] == $row['comment_author'])) { // Авторът на коментара също може

            $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
            if ($form_submit == 1) {
                $comment = trim(filter_input(INPUT_POST, 'comment')); // Премахнато addslashes()

                // Редактиране на коментара (с prepared statement)
                $sql_update = "UPDATE comments SET comment = :comment WHERE comment_id = :post_comment_id";
                $params_update = [
                    ":comment" => $comment,
                    ":post_comment_id" => $post_comment_id
                ];
                $result = run_q($sql_update, $params_update);

                if ($result) {
                    redirect('../topic.php?topic_id=' . $topic_id . '&cat_id=' . $cat_id); // Коригиран URL-а
                } else {
                    echo "Грешка при редактиране на коментара."; // Обработка на грешката
                }
            }
        } else {
            echo "Нямате права да редактирате този коментар.";
        }


    } else {
        echo "Грешка при извличане на коментара."; // Обработка на грешката
        exit; // Спираме изпълнението, ако няма резултат
    }

} else {
    echo "Не сте влезли в системата.";
    exit;
}

include '../template/header.php';
?>

<form action="edit_comment.php?comment_id=<?php echo $comment_id; ?>&topic_id=<?php echo $topic_id; ?>&cat_id=<?php echo $cat_id; ?>" method="POST">
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

    <textarea name="comment" id="comment" rows="20" cols="102"><?php echo htmlspecialchars($row['comment'], ENT_QUOTES); ?></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="hidden" name="post_cat_id" value="<?php echo $cat_id ?>">
    <input type="hidden" name="post_topic_id" value="<?php echo $topic_id ?>">
    <input type="hidden" name="post_comment_id" value="<?php echo $comment_id ?>">
    <input type="submit" value="Edit Comment">
    <a href="../topic.php?topic_id=<?php echo $topic_id; ?>&cat_id=<?php echo $cat_id; ?>">
        <input type="button" value="Cancel" />
    </a>
</form>

<div id="fileUploadModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; padding:20px; border:1px solid gray;">
    <input type="file" id="imageUpload" name="imageUpload">
    <button type="button" onclick="uploadImage()">Upload</button>
    <button type="button" onclick="closeFileUpload()">Cancel</button>
</div>

<script>
    function formatText(type) {
        const textarea = document.getElementById('comment');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);

        let formattedText = '';
        switch (type) {
            case 'bold':
                formattedText = `**${selectedText}**`;
                break;
            case 'italic':
                formattedText = `*${selectedText}*`;
                break;
            case 'link':
                const url = prompt("Enter URL:");
                if (url) {
                    formattedText = `[${selectedText || 'Link Text'}](${url})`;
                } else {
                    return; // Handle cancel
                }
                break;
            case 'code':
                formattedText = "```\n" + selectedText + "\n```";
                break;
            case 'heading':
                const level = prompt("Enter heading level (1-6):", "2");
                if (level && level >= 1 && level <= 6) {
                    formattedText = "#".repeat(level) + " " + selectedText;
                } else {
                    return; // Handle cancel or invalid input
                }
                break;
            case 'list':
                // Handles both ordered and unordered lists
                const listType = prompt("Enter list type (ol for ordered, ul for unordered):", "ul");
                if (listType === "ol") {
                    formattedText = selectedText.split('\n').map((line, index) => `${index + 1}. ${line}`).join('\n');
                } else { // Default to unordered list
                    formattedText = selectedText.split('\n').map(line => `- ${line}`).join('\n');
                }
                break;
            case 'quote':
                formattedText = selectedText.split('\n').map(line => '> ' + line).join('\n');
                break;
        }

        textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
        textarea.focus();
    }

    function insertImage() {
        const imageUrl = prompt('Моля, въведете URL на картинката:');
        if (imageUrl) {
            const textarea = document.getElementById('comment');
            const start = textarea.selectionStart;
            const imageTag = `![Image](${imageUrl})`;
            textarea.value = textarea.value.substring(0, start) + imageTag + textarea.value.substring(start);
            textarea.focus();
        }
    }
	
    function openFileUpload() {
        document.getElementById('fileUploadModal').style.display = 'block';
    }

    function closeFileUpload() {
        document.getElementById('fileUploadModal').style.display = 'none';
    }

    function uploadImage() {
        const fileInput = document.getElementById('imageUpload');
        const file = fileInput.files[0];

        if (file) {
            const formData = new FormData();
            formData.append('imageUpload', file);

            fetch('../upload.php', {  // Път към вашия upload скрипт
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(imagePath => {
                    const textarea = document.getElementById('comment');
                    const start = textarea.selectionStart;
                    const imageTag = `![Image](${imagePath})`;
                    textarea.value = textarea.value.substring(0, start) + imageTag + textarea.value.substring(start);
                    textarea.focus();
                    closeFileUpload();
            })
            .catch(error => {
                console.error('Грешка при качване на снимка:', error);
                alert('Възникна грешка при качването.');
            });
        } else {
            alert('Моля, изберете снимка за качване.');
        }
    }
</script>

<?php
include '../template/footer.php';
?>