<?php
require_once 'lang/init.php';
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
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $photo ? htmlspecialchars($photo['title']) : $lang['photo_not_found_title']; ?> - <?php echo $lang['header_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="lang-switcher">
                <a href="?id=<?php echo $photoId; ?>&lang=en">EN</a> | <a href="?id=<?php echo $photoId; ?>&lang=ru">RU</a>
            </div>
            <h1><?php echo $lang['header_title']; ?></h1>
            <p><?php echo $lang['header_subtitle']; ?></p>
        </div>
    </header>

    <main>
        <div class="container">
            <section>
                <?php if ($photo): ?>
                    <div class="photo-container">
                        <h2><?php echo htmlspecialchars($photo['title']); ?></h2>
                        <img src="uploads/<?php echo htmlspecialchars($photo['filename']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                        <p><?php echo nl2br(htmlspecialchars($photo['description'])); ?></p>
                        <div class="photo-actions">
                            <a href="uploads/<?php echo htmlspecialchars($photo['filename']); ?>" download class="download-btn"><?php echo $lang['download_button']; ?></a>
                            <form action="delete.php" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo $lang['delete_confirm']; ?>');">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="delete-btn"><?php echo $lang['delete_button']; ?></button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <p><?php echo $lang['photo_not_found_message']; ?></p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p><?php echo $lang['footer_text']; ?></p>
        </div>
    </footer>
</body>
</html>
