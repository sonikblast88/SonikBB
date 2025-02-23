<?php

if (isset($_GET['content'])) {
    $config_content = urldecode($_GET['content']);
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config.php Configuration Preview</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 900px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        h3 {
            text-align: center;
            margin-bottom: 10px;
            color: #555;
        }

        pre {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
            margin-bottom: 20px;
        }

        button {
            display: block;
            margin: 0 auto;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        a {
            display: block;
            margin-top: 20px;
            text-align: center;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Config.php Configuration Preview</h2>
    <h3>Please copy all the information to your config.php located in the root directory of SonikBB</h3>
    <pre id="config-content"><?php echo htmlspecialchars($config_content); ?></pre>
    <button id="copy-button">Copy Text</button>
    <a href="../index.php">Go To Website</a>
</div>

<script>
    const copyButton = document.getElementById('copy-button');
    const configContent = document.getElementById('config-content');

    copyButton.addEventListener('click', () => {
        const tempTextArea = document.createElement('textarea');
        tempTextArea.value = configContent.textContent;
        document.body.appendChild(tempTextArea);
        tempTextArea.select();
        document.execCommand('copy');
        document.body.removeChild(tempTextArea);

        copyButton.textContent = 'Copied!';
        setTimeout(() => {
            copyButton.textContent = 'Copy Text';
        }, 2000);
    });
</script>

</body>
</html>

    <?php
} else {
    echo "No configuration content provided.";
}

?>