<?php
require_once 'lang/init.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main>
        <div class="container">
            <section>
                <h2>My Profile</h2>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                <p>This is your profile page. More features to come!</p>
            </section>
        </div>
    </main>

    <footer class="container">
        <p><?php echo $lang['footer_text']; ?></p>
    </footer>
</body>
</html>
