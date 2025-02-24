<?php
// add_topic.php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);

// Вземане на ID на категорията от URL
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;

// Обработка на формата за добавяне на тема
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent = $cat_id;
    $topic_name = htmlspecialchars(strip_tags($_POST['topic_name']));
    $topic_desc = $_POST['topic_desc'];
    $topic_author = $_SESSION['user_id'] ?? 0; // Използване на ID на текущия потребител или 0, ако не е логнат

    if ($topicsModel->createTopic($parent, $topic_name, $topic_desc, $topic_author)) {
        header("Location: topics.php?cat_id=" . $parent);
        exit();
    } else {
        echo "Грешка при създаване на темата.";
    }
}

include ('template/header.php');
?>

<form method="POST" action="add_topic.php?cat_id=<?= $cat_id ?>" enctype="multipart/form-data">
	<label for="topic_name"><h2>Add Topic<h2></label>

    <input type="text" name="topic_name"  size="98" placeholder="Topic Name" required>
	
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
	
    <textarea id="topic_desc" name="topic_desc" placeholder="Topic Description" rows="20" cols="102" required></textarea>
    <button type="submit">Add Topic</button>
    <a href="topics.php?cat_id=<?= $cat_id ?>"><button type="button">Cancel</button></a>
	
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
                return;
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
                return; 
            }
            break;
        case 'list':
            formattedText = selectedText.split('\n').map(line => `- ${line}`).join('\n');
            break;
        case 'quote':
            formattedText = selectedText.split('\n').map(line => '> ' + line).join('\n');
            break;
    }

    textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
    textarea.focus();
}

function insertImage() {
    const imageUrl = prompt('Please enter the image URL:');
    if (imageUrl) {
        const textarea = document.getElementById('topic_desc');
        const start = textarea.selectionStart;
        const imageTag = `![Image](${imageUrl})`;
        textarea.value = textarea.value.substring(0, start) + imageTag + textarea.value.substring(start);
        textarea.focus();
    }
}

// This part is for uploading images, uses ../upload.php and uploads them to the uploads folder
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

        fetch('upload.php', {
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
            console.error('Error uploading image:', error);
            alert('An error occurred during upload.'); 
        });
    } else {
        alert('Please select an image to upload.'); 
    }
}
</script>

<?php
include 'template/footer.php';
?>
