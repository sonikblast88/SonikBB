<?php
include '../functions.php';

if (isset($_SESSION['is_loged'])) {
    $topic_id = (int)filter_input(INPUT_GET, 'topic_id');
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');

    if ($topic_id > 0) {
        // Извличане на данните за темата (с prepared statement)
        $sql_select = "SELECT topic_name, topic_desc FROM topics WHERE topic_id = :topic_id";
        $params_select = [":topic_id" => $topic_id];
        $stmt = run_q($sql_select, $params_select);

        if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($_SESSION['user_info']['type'] == 2 || // Админ винаги може да редактира
                (isset($_SESSION['user_info']['user_id']) && $_SESSION['user_info']['user_id'] == $row['topic_author'])) { // Авторът на темата също може

                $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
                if ($form_submit == 1) {
                    $topic_name = trim(filter_input(INPUT_POST, 'topic_name')); // Премахнато addslashes()
                    $topic_desc = trim(filter_input(INPUT_POST, 'topic_desc')); // Премахнато addslashes()
                    $post_cat_id = (int)filter_input(INPUT_POST, 'post_cat_id');
                    $post_topic_id = (int)filter_input(INPUT_POST, 'post_topic_id');

                    // Актуализация на темата (с prepared statement)
                    $sql_update = "UPDATE topics SET topic_name = :topic_name, topic_desc = :topic_desc WHERE topic_id = :post_topic_id";
                    $params_update = [
                        ":topic_name" => $topic_name,
                        ":topic_desc" => $topic_desc,
                        ":post_topic_id" => $post_topic_id
                    ];
                    $result = run_q($sql_update, $params_update);

                    if ($result) {
                        redirect('../topic.php?topic_id=' . $post_topic_id . '&cat_id=' . $post_cat_id);
                    } else {
                        echo "Грешка при редактиране на темата."; // Обработка на грешката
                    }
                }
            } else {
                echo "Нямате права да редактирате тази тема.";
            }

        } else {
            echo "Грешка при извличане на данните за темата.";
            exit;
        }
    } else {
        echo "Невалиден topic_id.";
        exit;
    }

} else {
    echo "Не сте влезли в системата.";
    exit;
}

include '../template/header.php';
?>

<form action="edit_topic.php?topic_id=<?php echo $topic_id; ?>&cat_id=<?php echo $cat_id; ?>" method="POST">
    <input type="text" name="topic_name" value="<?php echo htmlspecialchars($row['topic_name'], ENT_QUOTES); ?>" size="98" placeholder="Име на темата" required><br />
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
    <textarea name="topic_desc" id="topic_desc" rows="20" cols="102" required><?php echo htmlspecialchars($row['topic_desc'], ENT_QUOTES); ?></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="hidden" name="post_cat_id" value="<?php echo $cat_id; ?>">
    <input type="hidden" name="post_topic_id" value="<?php echo $topic_id; ?>">
    <input type="submit" value="Edit Topic">
    <a href="../topic.php?topic_id=<?php echo $topic_id; ?>&cat_id=<?php echo $cat_id; ?>">
        <input type="button" value="Cancel" />
    </a>

    <div id="fileUploadModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; padding:20px; border:1px solid gray;">
        <input type="file" id="imageUpload" name="imageUpload">
        <button type="button" onclick="uploadImage()">Upload</button>
        <button type="button" onclick="closeFileUpload()">Cancel</button>
    </div>
</form>

<script>
    function formatText(type) {
        const textarea = document.getElementById('topic_desc');
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
                formattedText = selectedText.split('\n').map(line => `- ${line}`).join('\n');
                break;
            case 'quote': //New Case for Quote
                formattedText = selectedText.split('\n').map(line => '> ' + line).join('\n');
                break;
        }

        textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
        textarea.focus();
    }

    function insertImage() {
        const imageUrl = prompt('Моля, въведете URL на картинката:');
        if (imageUrl) {
            const textarea = document.getElementById('topic_desc');
            const start = textarea.selectionStart;
            const imageTag = `![Image](${imageUrl})`;
            textarea.value = textarea.value.substring(0, start) + imageTag + textarea.value.substring(start);
            textarea.focus();
        }
    }

// тази част е за качването на снимки, ползва ../upload.php и ги качва в папка uploads	
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
					const textarea = document.getElementById('topic_desc');
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
// КРАЙ *** тази част е за качването на снимки, ползва ../upload.php и ги качва в папка uploads
</script>

<?php
include '../template/footer.php';
?>