<?php
require_once 'lang/init.php';
require_once 'config/db.php';

// --- Response helper ---
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// --- Auth & CSRF ---
if (!isset($_SESSION['user_id'])) {
    send_json_response(['success' => false, 'message' => 'You must be logged in to upload photos.'], 401);
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    send_json_response(['success' => false, 'message' => 'CSRF token validation failed.'], 403);
}

// --- Main logic ---
$user_id = $_SESSION['user_id'];
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (empty($title)) {
    send_json_response(['success' => false, 'message' => 'Title is required.'], 400);
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    send_json_response(['success' => false, 'message' => $lang['no_file_uploaded_message']], 400);
}

// --- Security Validations ---
$photo = $_FILES['photo'];
$max_file_size = 5 * 1024 * 1024; // 5MB

if ($photo['size'] > $max_file_size) {
    send_json_response(['success' => false, 'message' => $lang['file_too_large_message']], 400);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($photo['tmp_name']);
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mime_type, $allowed_mime_types)) {
    send_json_response(['success' => false, 'message' => $lang['invalid_mime_type_message']], 400);
}

$path_parts = pathinfo($photo['name']);
$extension = isset($path_parts['extension']) ? '.' . strtolower($path_parts['extension']) : '';
$filename_no_ext = preg_replace("/[^a-zA-Z0-9_-]/", "", $path_parts['filename']);
$safe_filename = uniqid() . '_' . $filename_no_ext . $extension;

$uploadDir = 'uploads/';
$targetFilePath = $uploadDir . $safe_filename;

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        send_json_response(['success' => false, 'message' => 'Failed to create uploads directory.'], 500);
    }
}

if (move_uploaded_file($photo['tmp_name'], $targetFilePath)) {
    $stmt = $conn->prepare("INSERT INTO photos (user_id, title, description, filename) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $safe_filename);

    if ($stmt->execute()) {
        $new_photo_id = $conn->insert_id;
        $response = [
            'success' => true,
            'message' => $lang['upload_success_message'],
            'photo' => [
                'id' => $new_photo_id,
                'title' => htmlspecialchars($title),
                'username' => htmlspecialchars($_SESSION['username']),
                'filename' => htmlspecialchars($safe_filename)
            ]
        ];
        send_json_response($response, 201);
    } else {
        error_log("Database error: " . $stmt->error);
        send_json_response(['success' => false, 'message' => 'A database error occurred.'], 500);
    }
    $stmt->close();
} else {
    send_json_response(['success' => false, 'message' => $lang['upload_error_message']], 500);
}

$conn->close();
?>
