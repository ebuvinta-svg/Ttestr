<?php
require_once 'lang/init.php';
require_once 'config/db.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = $_POST['title'];
    $description = $_POST['description'];

    // File upload handling
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Allow certain file formats
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($fileType), $allowedTypes)) {
            // Check if the uploads directory exists, if not create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move the file to the uploads directory
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                // Insert photo details into the database
                $stmt = $conn->prepare("INSERT INTO photos (title, description, filename) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $description, $fileName);

                if ($stmt->execute()) {
                    // Redirect to the gallery page
                    header("Location: index.php?upload=success&lang=" . $lang_code);
                    exit();
                } else {
                    echo "Error: " . $stmt->error; // This is a database error, so it's better to keep it in English for debugging
                }
                $stmt->close();
            } else {
                echo $lang['upload_error_message'];
            }
        } else {
            echo $lang['invalid_file_type_message'];
        }
    } else {
        echo $lang['no_file_uploaded_message'];
    }
} else {
    // If not a POST request, redirect to the homepage
    header("Location: index.php?lang=" . $lang_code);
    exit();
}

// Close the connection
$conn->close();
?>
