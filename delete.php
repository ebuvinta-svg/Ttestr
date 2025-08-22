<?php
require_once 'lang/init.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['photo_id'])) {
    header('Location: index.php');
    exit();
}

// CSRF Token validation
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // Redirect or show an error
    header('Location: index.php?deletion=error&reason=csrf');
    exit();
}

$photo_id = (int)$_POST['photo_id'];

// 1. Get the filename before deleting the DB record
$stmt = $conn->prepare("SELECT filename FROM photos WHERE id = ?");
$stmt->bind_param("i", $photo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Photo not found, maybe already deleted.
    header('Location: index.php?deletion=error');
    exit();
}
$photo = $result->fetch_assoc();
$filename = $photo['filename'];
$stmt->close();


// 2. Delete the record from the database
$stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
$stmt->bind_param("i", $photo_id);

if ($stmt->execute()) {
    // 3. If DB deletion is successful, delete the file
    $filepath = 'uploads/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath); // Deletes the file
    }
    // Redirect with success message
    header('Location: index.php?deletion=success');
} else {
    // Redirect with error message
    header('Location: index.php?deletion=error');
}

$stmt->close();
$conn->close();
?>
