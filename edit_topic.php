<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Topics.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "You must be logged in.";
    exit;
}

// Retrieve topic ID from URL and validate it
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
if ($topic_id <= 0) {
    http_response_code(400);
    echo "Invalid topic ID.";
    exit;
}

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);

// Retrieve topic information
$topic = $topicsModel->getTopicById($topic_id);

// Process the edit topic form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_name = htmlspecialchars(strip_tags($_POST['topic_name']));
    // Allow HTML/Markdown formatting for topic description
    $topic_desc = $_POST['topic_desc'];

    if ($topicsModel->updateTopic($topic_id, $topic_name, $topic_desc)) {
        header("Location: topic.php?topic_id=" . $topic['topic_id']);
        exit();
    } else {
        echo "Error updating topic.";
    }
}

include('template/header.php');
?>

<!-- Form for editing a topic -->
<form method="POST" action="edit_topic.php?topic_id=<?= $topic_id ?>">
	<label for="topic_name"><h2>Edit Topic</h2></label>
	<div class="input-container">
		<button type="button" onclick="insertIntoTitle('📢 ')">📢</button>
		<button type="button" onclick="insertIntoTitle('📝 ')">📝</button>
		<button type="button" onclick="insertIntoTitle('🔗 ')">🔗</button>
		<button type="button" onclick="insertIntoTitle('📎 ')">📎</button>
		<button type="button" onclick="insertIntoTitle('🖼️ ')">🖼️</button>
		<button type="button" onclick="insertIntoTitle('💬 ')">💬</button>
		<button type="button" onclick="insertIntoTitle('📌 ')">📌</button>
		<button type="button" onclick="insertIntoTitle('⚡ ')">⚡</button>
		<button type="button" onclick="insertIntoTitle('🚀 ')">🚀</button>
		<button type="button" onclick="insertIntoTitle('✅ ')">✅</button>
		<input type="text" name="topic_name" id="topic_name" value="<?= htmlspecialchars($topic['topic_name']) ?>" size="98" placeholder="Topic Name" required>
	</div>

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
    
    <textarea name="topic_desc" id="topic_desc" rows="20" cols="102" required><?= htmlspecialchars($topic['topic_desc']) ?></textarea>
    <button type="submit">Save Changes</button>
    <a href="topic.php?topic_id=<?= $topic['topic_id'] ?>"><button type="button">Cancel</button></a>
    
    <div id="fileUploadModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; padding:20px; border:1px solid gray;">
        <input type="file" id="imageUpload" name="imageUpload">
        <button type="button" onclick="uploadImage()">Upload</button>
        <button type="button" onclick="closeFileUpload()">Cancel</button>
    </div>
</form>

<script>
function insertIntoTitle(text) {
    const inputField = document.getElementById('topic_name');
    inputField.value += text;  // Adds the emoji to the input field
    inputField.focus();  // Keeps the cursor active in the input field
}
</script>

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

// Functions for uploading images using upload.php
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
