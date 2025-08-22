<?php
// Include the database connection file
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
            // Move the file to the uploads directory
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                // Insert photo details into the database
                $stmt = $conn->prepare("INSERT INTO photos (title, description, filename) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $description, $fileName);

                if ($stmt->execute()) {
                    // Redirect to the gallery page
                    header("Location: index.php?upload=success");
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
        }
    } else {
        echo "No file was uploaded or there was an upload error.";
    }
} else {
    // If not a POST request, redirect to the homepage
    header("Location: index.php");
    exit();
}

// Close the connection
$conn->close();
?>
