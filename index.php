<?php
require_once 'lang/init.php';
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="lang-switcher">
                <a href="?lang=en">EN</a> | <a href="?lang=ru">RU</a>
            </div>
            <h1><?php echo $lang['header_title']; ?></h1>
            <p><?php echo $lang['header_subtitle']; ?></p>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="upload-form">
                <h2><?php echo $lang['upload_form_title']; ?></h2>
                <form id="upload-form" action="upload.php" method="post" enctype="multipart/form-data">
                    <label for="title"><?php echo $lang['form_title_label']; ?></label>
                    <input type="text" name="title" id="title" required>

                    <label for="description"><?php echo $lang['form_description_label']; ?></label>
                    <textarea name="description" id="description" rows="4"></textarea>

                    <label for="photo"><?php echo $lang['form_photo_label']; ?></label>
                    <input type="file" name="photo" id="photo" accept="image/*" required>

                    <button type="submit"><?php echo $lang['upload_button']; ?></button>
                </form>
                <div id="upload-status"></div>
            </section>

            <section class="gallery">
                <h2><?php echo $lang['gallery_title']; ?></h2>
                <div id="gallery-container" class="gallery-container">
                    <?php
                    // Fetch photos from the database
                    $result = $conn->query("SELECT * FROM photos ORDER BY uploaded_at DESC");

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="gallery-item">';
                            echo '<a href="photo.php?id=' . $row['id'] . '&lang=' . $lang_code . '">';
                            // Use data-src for lazy loading
                            echo '<img class="lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="uploads/' . htmlspecialchars($row['filename']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="no-photos-message">' . $lang['no_photos_message'] . '</p>';
                    }
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

    <script src="js/main.js" defer></script>
</body>
</html>
