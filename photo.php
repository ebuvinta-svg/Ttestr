<?php
// Include the database connection file
require_once 'config/db.php';

// Check if the photo ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$photoId = $_GET['id'];

// Fetch photo details from the database
$stmt = $conn->prepare("SELECT * FROM photos WHERE id = ?");
$stmt->bind_param("i", $photoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $photo = $result->fetch_assoc();
} else {
    // No photo found with the given ID
    $photo = null;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $photo ? htmlspecialchars($photo['title']) : 'Photo not found'; ?> - Imagine</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Imagine</h1>
        <p>Your personal photo gallery.</p>
    </header>

    <main>
        <?php if ($photo): ?>
            <div class="photo-container">
                <h2><?php echo htmlspecialchars($photo['title']); ?></h2>
                <img src="uploads/<?php echo htmlspecialchars($photo['filename']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                <p><?php echo nl2br(htmlspecialchars($photo['description'])); ?></p>
                <a href="uploads/<?php echo htmlspecialchars($photo['filename']); ?>" download class="download-btn">Download Photo</a>
            </div>
        <?php else: ?>
            <p>Photo not found. <a href="index.php">Return to gallery</a>.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2023 Imagine</p>
    </footer>
</body>
</html>
