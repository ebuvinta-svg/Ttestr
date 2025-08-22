<?php
header('Content-Type: application/json');
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No photo ID provided.']);
    exit();
}

$photoId = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM photos WHERE id = ?");
$stmt->bind_param("i", $photoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $photo = $result->fetch_assoc();
    // Sanitize output
    foreach ($photo as $key => $value) {
        $photo[$key] = htmlspecialchars($value);
    }
    echo json_encode(['success' => true, 'photo' => $photo]);
} else {
    echo json_encode(['success' => false, 'message' => 'Photo not found.']);
}

$stmt->close();
$conn->close();
?>
