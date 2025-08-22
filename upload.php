<?php
require_once 'lang/init.php';
require_once 'config/db.php';

$is_ajax = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    } else {
        header("Location: index.php?lang=" . $lang_code);
    }
    exit();
}

// --- Response helper ---
function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}


// --- Main logic ---
$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';

if (empty($title)) {
    send_json_response(['success' => false, 'message' => 'Title is required.']);
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    send_json_response(['success' => false, 'message' => $lang['no_file_uploaded_message']]);
}

$uploadDir = 'uploads/';
$fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
$targetFilePath = $uploadDir . $fileName;
$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array(strtolower($fileType), $allowedTypes)) {
    send_json_response(['success' => false, 'message' => $lang['invalid_file_type_message']]);
}

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        send_json_response(['success' => false, 'message' => 'Failed to create uploads directory.']);
    }
}

if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
    $stmt = $conn->prepare("INSERT INTO photos (title, description, filename) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $fileName);

    if ($stmt->execute()) {
        $new_photo_id = $conn->insert_id;
        $response = [
            'success' => true,
            'message' => $lang['upload_success_message'],
            'photo' => [
                'id' => $new_photo_id,
                'title' => htmlspecialchars($title),
                'filename' => htmlspecialchars($fileName)
            ]
        ];
        send_json_response($response);
    } else {
        // Log the detailed error, but send a generic message to the user
        error_log("Database error: " . $stmt->error);
        send_json_response(['success' => false, 'message' => 'A database error occurred.']);
    }
    $stmt->close();
} else {
    send_json_response(['success' => false, 'message' => $lang['upload_error_message']]);
}

$conn->close();
?>
