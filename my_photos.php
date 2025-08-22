<?php
require_once 'lang/init.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/db.php';
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Photos - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-csrf-token="<?php echo $csrf_token; ?>" data-lang-js='<?php echo json_encode([
    "download_button" => $lang["download_button"],
    "delete_button" => $lang["delete_button"],
    "delete_confirm" => $lang["delete_confirm"]
]); ?>'>
    <?php include 'partials/header.php'; ?>

    <main>
        <div class="container">
            <section class="gallery">
                <h2>My Photos</h2>
                <div id="gallery-container" class="gallery-container">
                    <?php
                    $stmt = $conn->prepare("SELECT id, filename, title FROM photos WHERE user_id = ? ORDER BY uploaded_at DESC");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="gallery-item">';
                            echo '<a href="photo.php?id=' . $row['id'] . '&lang=' . $lang_code . '">';
                            echo '<img class="lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="uploads/' . htmlspecialchars($row['filename']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="no-photos-message">You have not uploaded any photos yet.</p>';
                    }
                    $stmt->close();
                    ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p><?php echo $lang['footer_text']; ?></p>
        </div>
    </footer>

    <div id="lightbox-modal" class="lightbox-modal">
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <div id="lightbox-photo-container"></div>
        </div>
    </div>

    <script src="js/main.js" defer></script>
    <div id="toast-container"></div>
</body>
</html>
