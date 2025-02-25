<?php
session_start();
include_once 'core/autoload.php';
include_once 'models/Users.php';
include_once 'models/Topics.php';

$database = new Database();
$db = $database->connect();
$usersModel = new Users($db);
$topicsModel = new Topics($db);

$get_profile_id = filter_input(INPUT_GET, 'profile_id', FILTER_SANITIZE_NUMBER_INT);

// Check if user is logged in and is editing their own profile
if (!isset($_SESSION['is_loged']) || $_SESSION['user_id'] !== $get_profile_id) {
    header("Location: index.php");
    exit;
}

$user = $usersModel->getUserById($get_profile_id);
if (!$user) {
    die("User not found.");
}

$successMessage = ""; // Initialize success message variable

// Password change form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submit'])) {
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

// Signature form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_signature'])) {
    $signature = trim($_POST['signature']);
    $usersModel->updateSignature($get_profile_id, $signature);
    $successMessage = "Signature changed successfully.";
}

// Avatar upload form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imageUpload'])) {
    $result = $usersModel->uploadAvatar($get_profile_id, $_FILES['imageUpload']);

    if ($result === true) {
        $successMessage = "Avatar changed successfully. Please log out and log back in to apply changes.";
    } else {
        $error = $result;
        echo $error;
    }
}

include 'template/header.php';
?>
<div id="content">
    <h2>Edit Profile</h2>
    <p>Hello, <b><?= htmlspecialchars($user['username']) ?></b>. You can edit your profile here.</p>

    <?php if (!empty($successMessage)): ?>
        <p style="color: green;"><?= $successMessage ?></p>
    <?php endif; ?>

    <h3>Change Password</h3>
    <form method="post" action="profile.php?profile_id=<?= $get_profile_id ?>">
        <input type="password" name="old_password" placeholder="Old Password" required><br>
        <input type="password" name="new_password" placeholder="New Password" required><br>
        <input type="password" name="match_password" placeholder="Repeat New Password" required><br>
        <input type="hidden" name="form_submit" value="1">
        <button type="submit">Change Password</button>
    </form>

    <h3>Edit Signature</h3>
    <form method="post" action="profile.php?profile_id=<?= $get_profile_id ?>">
        <textarea name="signature" rows="4" cols="50"><?= htmlspecialchars($user['signature']) ?></textarea><br>
        <input type="hidden" name="submit_signature" value="1">
        <button type="submit">Save Signature</button>
    </form>

    <h3>Change Avatar</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="imageUpload" accept="image/*" required><br>
        <button type="submit">Upload Avatar</button>
    </form>
</div>

<?php
include_once('aside.php');
include_once 'template/footer.php';
?>
