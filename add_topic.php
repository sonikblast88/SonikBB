<?php
session_start();

// Check if the user is logged in; if not, send a 401 response.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "You must be logged in to add a topic.";
    exit;
}

// Retrieve and validate the category ID from the URL.
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
if ($cat_id <= 0) {
    http_response_code(400);
    echo "Invalid category ID.";
    exit;
}

include_once 'core/autoload.php';
include_once 'models/Topics.php';

$database = new Database();
$db = $database->connect();

$topicsModel = new Topics($db);

// Process the form submission for adding a new topic.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent = $cat_id;
    $topic_name = htmlspecialchars($_POST['topic_name']);
    // Allow HTML/Markdown in the topic description.
    $topic_desc = $_POST['topic_desc'];
    // Use the current user's ID as the topic author.
    $topic_author = $_SESSION['user_id'] ?? 0;

    // Call createTopic, which returns the new topic ID on success.
    $new_topic_id = $topicsModel->createTopic($parent, $topic_name, $topic_desc, $topic_author);
    if ($new_topic_id) {
        header("Location: topic.php?topic_id=" . $new_topic_id);
        exit();
    } else {
        echo "Error creating topic.";
    }
}

include('template/header.php');
?>

<form method="POST" action="add_topic.php?cat_id=<?= $cat_id ?>" enctype="multipart/form-data">

	<label for="topic_name"><h2>Add Topic</h2></label>
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
		<input type="text" id="topic_name" name="topic_name" size="98" placeholder="Topic Name" required>
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
		<button type="button" onclick="insertYouTube()">YouTube</button>
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
function insertIntoTitle(text) {
    const inputField = document.getElementById('topic_name');
    inputField.value += text;  // Добавя иконката в края на полето
    inputField.focus();  // Остава курсора активен в полето
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

function insertYouTube() {
    const videoId = prompt("Enter YouTube Video ID (e.g., zBI0v2vOEaY):");
    if (videoId) {
        const textarea = document.getElementById('topic_desc');
        const start = textarea.selectionStart;
        const youtubeEmbed = `[![Watch the video](https://img.youtube.com/vi/${videoId}/maxresdefault.jpg)](https://www.youtube.com/watch?v=${videoId})`;
        textarea.value = textarea.value.substring(0, start) + youtubeEmbed + textarea.value.substring(start);
        textarea.focus();
    }
}
</script>

<?php
include 'template/footer.php';
?>
