<?php
include '../functions.php';

if (isset($_GET['cat_id'], $_GET['action']) && ($_GET['action'] == 'up' || $_GET['action'] == 'down')) {
    $cat_id = (int)$_GET['cat_id']; 
    $action = $_GET['action'];

    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->beginTransaction(); // Start transaction for data integrity

        // Fetch the current position of the category (prepared statement)
        $stmt = $conn->prepare("SELECT position FROM categories WHERE cat_id = :cat_id");
        $stmt->execute([':cat_id' => $cat_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $currentPosition = $row['position'];

            // Determine the new position
            $newPosition = ($action == 'up') ? $currentPosition - 1 : $currentPosition + 1;

            // Check for potential conflicts (categories with the new position)
            $checkStmt = $conn->prepare("SELECT cat_id FROM categories WHERE position = :newPosition");
            $checkStmt->execute([':newPosition' => $newPosition]);

            if ($checkStmt->rowCount() > 0) {
                // If a category exists at the new position, swap positions
                $swapStmt = $conn->prepare("UPDATE categories SET position = :currentPosition WHERE position = :newPosition");
                $swapParams = [
                    ':currentPosition' => $currentPosition,
                    ':newPosition' => $newPosition
                ];
                $swapStmt->execute($swapParams);

                $updateStmt = $conn->prepare("UPDATE categories SET position = :newPosition WHERE cat_id = :cat_id");
                $updateParams = [
                    ':newPosition' => $newPosition,
                    ':cat_id' => $cat_id
                ];
                $updateStmt->execute($updateParams);

            } else {
                $updateStmt = $conn->prepare("UPDATE categories SET position = :newPosition WHERE cat_id = :cat_id");
                $updateParams = [
                    ':newPosition' => $newPosition,
                    ':cat_id' => $cat_id
                ];
                $updateStmt->execute($updateParams);
            }

            $conn->commit();
            header('Location: ../index.php'); // Redirect after successful update
            exit; // Important to stop further script execution

        } else {
            echo 'Category not found.';
        }

    } catch (PDOException $e) {
        $conn->rollBack(); // Rollback on error
        echo 'Error updating position: ' . $e->getMessage();
    }
} else {
    echo 'Invalid request parameters.';
}

?>
