<?php
require_once 'lang/init.php';
require_once 'config/db.php';

// --- Response helper ---
function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['success' => false, 'message' => 'Invalid request method.']);
}

// --- Main logic ---

// CSRF Token validation
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    send_json_response(['success' => false, 'message' => 'CSRF token validation failed.']);
}

$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';

if (empty($title)) {
    send_json_response(['success' => false, 'message' => 'Title is required.']);
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    send_json_response(['success' => false, 'message' => $lang['no_file_uploaded_message']]);
}

// --- Security Validations ---
$photo = $_FILES['photo'];
$max_file_size = 5 * 1024 * 1024; // 5MB

// 1. File size check
if ($photo['size'] > $max_file_size) {
    send_json_response(['success' => false, 'message' => $lang['file_too_large_message']]);
}

// 2. MIME type check
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($photo['tmp_name']);
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mime_type, $allowed_mime_types)) {
    send_json_response(['success' => false, 'message' => $lang['invalid_mime_type_message']]);
}

// 3. Sanitize filename
$path_parts = pathinfo($photo['name']);
$extension = isset($path_parts['extension']) ? '.' . strtolower($path_parts['extension']) : '';
$filename_no_ext = preg_replace("/[^a-zA-Z0-9_-]/", "", $path_parts['filename']);
$safe_filename = uniqid() . '_' . $filename_no_ext . $extension;


$uploadDir = 'uploads/';
$targetFilePath = $uploadDir . $safe_filename;

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        send_json_response(['success' => false, 'message' => 'Failed to create uploads directory.']);
    }
}

if (move_uploaded_file($photo['tmp_name'], $targetFilePath)) {
    $stmt = $conn->prepare("INSERT INTO photos (title, description, filename) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $safe_filename);

    if ($stmt->execute()) {
        $new_photo_id = $conn->insert_id;
        $response = [
            'success' => true,
            'message' => $lang['upload_success_message'],
            'photo' => [
                'id' => $new_photo_id,
                'title' => htmlspecialchars($title),
                'filename' => htmlspecialchars($safe_filename)
            ]
        ];
        send_json_response($response);
    } else {
        error_log("Database error: " . $stmt->error);
        send_json_response(['success' => false, 'message' => 'A database error occurred.']);
    }
    $stmt->close();
} else {
    send_json_response(['success' => false, 'message' => $lang['upload_error_message']]);
}

$conn->close();
?>
