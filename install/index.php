<?php

// Configuration data
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = '';
$path = './';
$site_title = 'My Forum';
$website = "http://yourwebsite.com";
$site_version = "0.0.20 Dev";
$excluded_files = "'.htaccess', 'config.php', 'README.md', 'скрит_файл.zip', 'index.php', 'download_counts.txt'";

$errors = [];

if (isset($_POST['submit'])) {
    $db_host = $_POST['db_host'];
    $db_username = $_POST['db_username'];
    $db_password = $_POST['db_password'];
    $db_name = $_POST['db_name'];
    $path = $_POST['path'];
    $site_title = $_POST['site_title'];
    $website = $_POST['website'];
    $admin_username = $_POST['admin_username'];
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

    // Data validation
    if (empty($db_host)) {
        $errors[] = 'Database Host is required.';
    }
    if (empty($db_username)) {
        $errors[] = 'Database Username is required.';
    }
    if (empty($db_name)) {
        $errors[] = 'Database Name is required.';
    }
    if (empty($path)) {
        $errors[] = 'Forum Path is required.';
    }
    if (empty($site_title)) {
        $errors[] = 'Site Title is required.';
    }
    if (empty($website)) {
        $errors[] = 'Website URL is required.';
    }
    if (empty($admin_username)) {
        $errors[] = 'Admin Username is required.';
    }
    if (empty($_POST['admin_password'])) {
        $errors[] = 'Admin Password is required.';
    }

    if (empty($errors)) {
        $conn = @mysqli_connect($db_host, $db_username, $db_password);
        if (!$conn) {
            $errors[] = 'Could not connect to the database. Please check the credentials.';
        } else {
            $db_host = mysqli_real_escape_string($conn, $db_host);
            $db_username = mysqli_real_escape_string($conn, $db_username);
            $db_name = mysqli_real_escape_string($conn, $db_name);
            $path = mysqli_real_escape_string($conn, $path);

            $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
            if (!mysqli_query($conn, $sql)) {
                $errors[] = 'Error creating database: ' . mysqli_error($conn);
            } else {
                mysqli_select_db($conn, $db_name);

                mysqli_begin_transaction($conn);
                $sql_tables = file_get_contents("database.sql");
                $queries = explode(";", $sql_tables);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        if (!mysqli_query($conn, $query)) {
                            $errors[] = 'Error creating table: ' . mysqli_error($conn) . " - Query: " . $query;
                            mysqli_rollback($conn);
                            break;
                        }
                    }
                }

                if (empty($errors)) {
                    // Insert admin user
                    $insert_admin_sql = "INSERT INTO `users` (`username`, `password`, `type`, `last_login`, `email`)
                                        VALUES ('$admin_username', '$admin_password', 2, NOW(), 'admin@example.com')"; // You might want to add an email field to the form
                    if (!mysqli_query($conn, $insert_admin_sql)) {
                        $errors[] = "Error inserting admin user: " . mysqli_error($conn);
                        mysqli_rollback($conn);
                    } else {
                        mysqli_commit($conn);

                        $config_content = "<?php\n\n";
                        $config_content .= "// Database settings\n";
                        $config_content .= "define('DB_HOST', '$db_host');\n";
                        $config_content .= "define('DB_USERNAME', '$db_username');\n";
                        $config_content .= "define('DB_PASSWORD', '$db_password');\n";
                        $config_content .= "define('DB_NAME', '$db_name');\n\n";
                        $config_content .= "// Forum path\n";
                        $config_content .=  '$path = ' . "'$path';\n\n";
                        $config_content .= "// Other settings\n";
                        $config_content .= "define('WEBSITE', '$website');\n";
                        $config_content .= "define('SITE_TITLE', '$site_title');\n";
						$config_content .= "define('SITE_VERSION', '$site_version');\n";
                        $config_content .= "date_default_timezone_set('Europe/Sofia');\n\n";
						$config_content .= "define('EXCLUDED_FILES', array($excluded_files));\n\n";
                        $config_content .= "?>";

                        $encoded_content = urlencode($config_content);

                        header("Location: config_preview.php?content=$encoded_content");
                        exit();
                    }
                }
            }
            mysqli_close($conn);
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Forum Installer</title>
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
            width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 12px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
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
        <input type="text" name="db_host" id="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required><br><br>

        <label for="db_username">Database Username:</label><br>
        <input type="text" name="db_username" id="db_username" value="<?php echo htmlspecialchars($db_username); ?>" required><br><br>

        <label for="db_password">Database Password:</label><br>
        <input type="password" name="db_password" id="db_password" required><br><br>

        <label for="db_name">Database Name:</label><br>
        <input type="text" name="db_name" id="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required><br><br>

        <label for="path">Forum Path (e.g., ./ or /sonikbb/):</label><br>
        <input type="text" name="path" id="path" value="<?php echo htmlspecialchars($path); ?>" required><br><br>

        <label for="site_title">Site Title:</label><br>
        <input type="text" name="site_title" id="site_title" value="<?php echo htmlspecialchars($site_title); ?>" required><br><br>

        <label for="website">Website URL:</label><br>
        <input type="text" name="website" id="website" value="<?php echo htmlspecialchars($website); ?>" required><br><br>

        <label for="admin_username">Admin Username:</label><br>
        <input type="text" name="admin_username" id="admin_username" required><br><br>

        <label for="admin_password">Admin Password:</label><br>
        <input type="password" name="admin_password" id="admin_password" required><br><br>

        <input type="submit" name="submit" value="Install">
    </form>
</div>

</body>
</html>