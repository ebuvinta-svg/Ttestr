<?php
require_once 'lang/init.php';
require_once 'config/db.php';

// --- Auth & CSRF ---
if (!isset($_SESSION['user_id'])) {
    // Not logged in
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['photo_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: index.php?deletion=error&reason=csrf');
    exit();
}

// --- Main logic ---
$photo_id = (int)$_POST['photo_id'];
$user_id = $_SESSION['user_id'];

// 1. Get the filename and verify ownership
$stmt = $conn->prepare("SELECT filename, user_id FROM photos WHERE id = ?");
$stmt->bind_param("i", $photo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php?deletion=error&reason=notfound');
    exit();
}

$photo = $result->fetch_assoc();
if ($photo['user_id'] !== $user_id) {
    // User does not own this photo
    header('Location: index.php?deletion=error&reason=permission');
    exit();
}

$filename = $photo['filename'];
$stmt->close();


// 2. Delete the record from the database
$stmt = $conn->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $photo_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    // 3. If DB deletion is successful, delete the file
    $filepath = 'uploads/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    header('Location: index.php?deletion=success');
} else {
    header('Location: index.php?deletion=error&reason=db');
}

$stmt->close();
$conn->close();
?>
