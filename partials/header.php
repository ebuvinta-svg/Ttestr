<?php
// This partial assumes that lang/init.php has been included before it.
?>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <h1><a href="index.php"><?php echo $lang['header_title']; ?></a></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="my_photos.php">My Photos</a></li>
                        <li><a href="profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="lang-switcher">
                <a href="?lang=en">EN</a> | <a href="?lang=ru">RU</a>
            </div>
        </div>
    </div>
</header>
