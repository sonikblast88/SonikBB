<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "You must be logged in to comment.";
    exit;
}

// Retrieve topic ID from URL
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
if ($topic_id <= 0) {
    http_response_code(400);
    echo "Invalid topic ID.";
    exit;
}

include_once 'core/autoload.php';
include_once 'models/Comments.php';

$database = new Database();
$db = $database->connect();

$commentsModel = new Comments($db);

// Process the form for adding a comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];
    $comment_author = $_SESSION['user_id'];

    if ($commentsModel->addComment($topic_id, $comment, $comment_author)) {
        header("Location: topic.php?topic_id=" . $topic_id);
        exit();
    } else {
        echo "Error adding comment.";
    }
}

include 'template/header.php';
?>

<form method="POST" action="add_comment.php?topic_id=<?= $topic_id ?>">
    <label for="comment"><h2>Add Comment</h2></label>
    
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
    
    <textarea name="comment" id="topic_desc" placeholder="Your comment" rows="20" cols="102" required></textarea>
    <button type="submit">Post Comment</button>
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

// Functions for image uploading using upload.php
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
