<?php

// config.php - Configuration file for the forum installer

// Database credentials
$db_host = '';
$db_username = '';
$db_password = '';
$db_name = '';

// Forum path
$path = './';

// Site title
$site_title = 'My Forum';

// Error messages
$errors = [];

// Check if the form is submitted
if (isset($_POST['submit'])) {

    // Get the form data
    $db_host = $_POST['db_host'];
    $db_username = $_POST['db_username'];
    $db_password = $_POST['db_password'];
    $db_name = $_POST['db_name'];
    $path = $_POST['path'];
    $site_title = $_POST['site_title'];

    // Validate the form data
    if (empty($db_host)) {
        $errors[] = 'Database host is required.';
    }
    if (empty($db_username)) {
        $errors[] = 'Database username is required.';
    }
    if (empty($db_name)) {
        $errors[] = 'Database name is required.';
    }

    // If there are no errors, create the database and tables
    if (empty($errors)) {

        // Create the database connection
        $conn = mysqli_connect($db_host, $db_username, $db_password);

        // Check the database connection
        if (!$conn) {
            $errors[] = 'Could not connect to the database: ' . mysqli_connect_error();
        } else {

            // Create the database if it doesn't exist
            $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
            if (!mysqli_query($conn, $sql)) {
                $errors[] = 'Error creating database: ' . mysqli_error($conn);
            } else {
                mysqli_select_db($conn, $db_name);
            }

            // Create the tables
            $sql_tables = file_get_contents("database.sql"); // Assuming you have a database.sql file with the SQL queries to create the tables
            $queries = explode(";", $sql_tables);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    if (!mysqli_query($conn, $query)) {
                        $errors[] = 'Error creating table: ' . mysqli_error($conn);
                    }
                }
            }

            // Create the config.php file
            $config_content = "<?php\n\n";
            $config_content .= "// Database credentials\n";
            $config_content .= "define('DB_HOST', '$db_host');\n";
            $config_content .= "define('DB_USERNAME', '$db_username');\n";
            $config_content .= "define('DB_PASSWORD', '$db_password');\n";
            $config_content .= "define('DB_NAME', '$db_name');\n\n";

            $config_content .= "// Forum path\n";
            $config_content .= "\$path = '$path';\n\n";

            $config_content .= "// Site title\n";
            $config_content .= "define('SITE_TITLE', '$site_title');\n\n";

            $config_content .= "// Other settings\n";
            $config_content .= "date_default_timezone_set('Europe/Sofia');\n\n"; // часова зона
            $config_content .= "?>";

            if (!file_put_contents('config.php', $config_content)) {
                $errors[] = 'Error creating config.php file.';
            }

            // If there are no errors, display a success message
            if (empty($errors)) {
                echo '<div class="success">Installation successful!</div>';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Forum Installer</title>
    <style>
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

    <h1>Forum Installer</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <label for="db_host">Database Host:</label><br>
        <input type="text" name="db_host" id="db_host" value="<?php echo $db_host; ?>" required><br><br>

        <label for="db_username">Database Username:</label><br>
        <input type="text" name="db_username" id="db_username" value="<?php echo $db_username; ?>" required><br><br>

        <label for="db_password">Database Password:</label><br>
        <input type="password" name="db_password" id="db_password"><br><br>

        <label for="db_name">Database Name:</label><br>
        <input type="text" name="db_name" id="db_name" value="<?php echo $db_name; ?>" required><br><br>

        <label for="path">Forum Path (e.g., ./ or /sonikbb/):</label><br>
        <input type="text" name="path" id="path" value="<?php echo $path; ?>" required><br><br>

        <label for="site_title">Site Title:</label><br>
        <input type="text" name="site_title" id="site_title" value="<?php echo $site_title; ?>" required><br><br>

        <input type="submit" name="submit" value="Install">
    </form>

</body>
</html>