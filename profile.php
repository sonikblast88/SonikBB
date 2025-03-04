<?php
session_start();

include_once 'core/autoload.php';
include_once 'models/Users.php';
include_once 'models/Topics.php';

$database = new Database();
$db = $database->connect();
$usersModel = new Users($db);
$topicsModel = new Topics($db);

$get_profile_id = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user is logged in and is editing their own profile
if (!isset($_SESSION['is_loged']) || (int)$_SESSION['user_id'] !== $get_profile_id) {
    header("Location: index.php");
    exit;
}

$user = $usersModel->getUserById($get_profile_id);
if (!$user) {
    die("User not found.");
}

$successMessage = "";
$error = "";

// Process password change form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $match_password = trim($_POST['match_password']);

    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $match_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $usersModel->updatePassword($get_profile_id, $hashed_password);
            $successMessage = "Password changed successfully. Please log out and log back in to apply changes.";
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Incorrect old password.";
    }
}

// Process signature update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_signature'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    
    $signature = trim($_POST['signature']);
    if ($usersModel->updateSignature($get_profile_id, $signature)) {
        $_SESSION['signature'] = $signature;
        $user = $usersModel->getUserById($get_profile_id);
        $successMessage = "Signature changed successfully.";
    } else {
        $error = "Error updating signature.";
    }
}

// Process avatar upload form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imageUpload'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    
    $result = $usersModel->uploadAvatar($get_profile_id, $_FILES['imageUpload']);

    if ($result === true) {
        $user = $usersModel->getUserById($get_profile_id);
        $_SESSION['avatar'] = $user['avatar'];
        $successMessage = "Avatar changed successfully.";
    } else {
        $error = $result;
    }
}

include 'template/header.php';
?>
<div id="content">
    <h2>Edit Profile</h2>
    <p>Hello, <b><?= htmlspecialchars($user['username']) ?></b>. You can edit your profile here.</p>

    <?php if (!empty($successMessage)): ?>
        <p style="color: green;"> <?= htmlspecialchars($successMessage) ?> </p>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <p style="color: red;"> <?= htmlspecialchars($error) ?> </p>
    <?php endif; ?>

    <h3>Change Password</h3>
    <form method="post" action="profile.php?profile_id=<?= $get_profile_id ?>">
        <input type="password" name="old_password" placeholder="Old Password" required><br>
        <input type="password" name="new_password" placeholder="New Password" required><br>
        <input type="password" name="match_password" placeholder="Repeat New Password" required><br>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="form_submit" value="1">
        <button type="submit">Change Password</button>
    </form>

    <h3>Edit Signature</h3>
    <form method="post" action="profile.php?profile_id=<?= $get_profile_id ?>">
        <textarea name="signature" rows="4" cols="50"><?= htmlspecialchars($user['signature']) ?></textarea><br>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="submit_signature" value="1">
        <button type="submit">Save Signature</button>
    </form>

    <h3>Change Avatar</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="imageUpload" accept="image/*" required><br>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit">Upload Avatar</button>
    </form>
</div>

<?php
include_once('aside.php');
include_once 'template/footer.php';
?>