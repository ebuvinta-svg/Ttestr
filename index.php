<?php
// Include the database connection file
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imagine - Photo Hosting</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Imagine</h1>
        <p>Your personal photo gallery.</p>
    </header>

    <main>
        <section class="upload-form">
            <h2>Upload a new photo</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="4"></textarea>

                <label for="photo">Choose a photo:</label>
                <input type="file" name="photo" id="photo" accept="image/*" required>

                <button type="submit">Upload Photo</button>
            </form>
        </section>

        <section class="gallery">
            <h2>Gallery</h2>
            <div class="gallery-container">
                <?php
                // Fetch photos from the database
                $result = $conn->query("SELECT * FROM photos ORDER BY uploaded_at DESC");

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="gallery-item">';
                        echo '<a href="photo.php?id=' . $row['id'] . '">';
                        echo '<img src="uploads/' . htmlspecialchars($row['filename']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No photos uploaded yet. Be the first to upload!</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Imagine</p>
    </footer>
</body>
</html>
