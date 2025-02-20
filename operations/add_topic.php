<?php
include '../functions.php';

if (isset($_SESSION['is_loged']) && isset($_SESSION['user_info']) && isset($_SESSION['user_info']['type']) && ($_SESSION['user_info']['type'] == 1 || $_SESSION['user_info']['type'] == 2)){
    $cat_id = (int)filter_input(INPUT_GET, 'cat_id');
    $post_cat_id = (int)filter_input(INPUT_POST, 'post_cat_id');
    $form_submit = (int)filter_input(INPUT_POST, 'form_submit');
    $topic_name = trim(filter_input(INPUT_POST, 'topic_name')); // No need for addslashes() with prepared statements
    $topic_desc = trim(filter_input(INPUT_POST, 'topic_desc')); // No need for addslashes() with prepared statements

    if ($form_submit == 1) {
        // Using prepared statement
        $sql = "INSERT INTO topics (parent, topic_name, topic_desc, topic_author) VALUES (:post_cat_id, :topic_name, :topic_desc, :user_id)";
        $params = [
            ":post_cat_id" => $post_cat_id,
            ":topic_name" => $topic_name,
            ":topic_desc" => $topic_desc,
            ":user_id" => $_SESSION['user_info']['user_id']
        ];
        $result = run_q($sql, $params);

        if ($result) { // Check if the query was successful
            redirect('../topics.php?cat_id=' . $post_cat_id);
        } else {
            echo "Error adding topic."; // Error handling
        }
    }
} else {
    // If not a logged-in user, redirect to index.php or another page
    redirect('../index.php'); // Or another appropriate page
    exit; // Important to use exit after redirect to stop script execution
}

include '../template/header.php';
?>

<form action="add_topic.php?cat_id=<?php echo $cat_id; ?>" method="POST">
    <input type="text" name="topic_name" value="" size="98" placeholder="Topic Name" required><br />
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

    <textarea name="topic_desc" id="topic_desc" placeholder="Topic Description..." rows="20" cols="102" required></textarea><br />
    <input type="hidden" name="form_submit" value="1">
    <input type="hidden" name="post_cat_id" value="<?php echo $cat_id; ?>">
    <input type="submit" value="Add Topic">
    <a href="../topics.php?cat_id=<?php echo $cat_id; ?>"><input type="button" value="Cancel" /></a>

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
            case 'quote': // New Case for Quote
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

            fetch('../upload.php', { // Path to your upload script
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
include '../template/footer.php';
?>