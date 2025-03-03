<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Comments.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "You must be logged in.";
    exit;
}

// Retrieve comment_id and topic_id from URL
$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

// Validate comment_id and topic_id
if ($comment_id <= 0 || $topic_id <= 0) {
    http_response_code(400);
    echo "Invalid parameters.";
    exit;
}

$database = new Database();
$db = $database->connect();

$commentsModel = new Comments($db);

// Retrieve comment information
$commentData = $commentsModel->getCommentById($comment_id);

// Check if the user has permission to edit the comment (either an admin or the comment author)
if ($_SESSION['type'] != 2 && $commentData['comment_author'] != $_SESSION['user_id']) {
    echo "You do not have permission to edit this comment.";
    exit;
}

// Process the edit comment form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];

    if ($commentsModel->updateComment($comment_id, $comment)) {
        header("Location: topic.php?topic_id=" . $topic_id);
        exit();
    } else {
        echo "Error editing comment.";
    }
}

include 'template/header.php';
?>

<form method="POST" action="edit_comment.php?comment_id=<?= $comment_id ?>&topic_id=<?= $topic_id ?>">
    <label for="topic_desc"><h2>Edit Comment</h2></label>
    
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
    
    <textarea name="comment" id="topic_desc" rows="20" cols="102" required><?= htmlspecialchars($commentData['comment']) ?></textarea>
    <button type="submit">Save Changes</button>
    <a href="topic.php?topic_id=<?= $topic_id ?>"><button type="button">Cancel</button></a>
    
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

// Functions for uploading images via upload.php
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
