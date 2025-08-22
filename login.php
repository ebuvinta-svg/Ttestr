<?php
require_once 'lang/init.php';
require_once 'config/db.php';

// If user is already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'CSRF token validation failed.';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username)) { $errors[] = 'Username is required.'; }
        if (empty($password)) { $errors[] = 'Password is required.'; }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username); // Allow login with username or email
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    session_regenerate_id(true); // Prevent session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];

                    header('Location: index.php?login=success');
                    exit();
                } else {
                    $errors[] = 'Invalid username or password.';
                }
            } else {
                $errors[] = 'Invalid username or password.';
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main>
        <div class="container">
            <section class="auth-form">
                <h2>Login</h2>
                <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
                    <div class="success-message">Registration successful! You can now log in.</div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
                <p>Don't have an account? <a href="register.php">Register here</a>.</p>
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
