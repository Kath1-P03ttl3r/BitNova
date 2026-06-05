<?php
// simple login handler - beginner comments added for clarity
require_once 'db.php';
$error = '';
// If already logged in, go straight to dashboard
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get and sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // basic validation of inputs - keep messages simple for beginners
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // look up user by username or email
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // check password (passwords are hashed when stored)
        if ($user && password_verify($password, $user['password_hash'])) {
            // set minimal session info for logged in user
            // only store id and username to keep session small
            $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
            header('Location: dashboard.php');
            exit;
        }
        // generic error to avoid revealing which part failed
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="auth-shell">
        <div class="auth-card">
            <h1>Login</h1>
            <?php if ($error): ?>
                <!-- show validation or auth error messages -->
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <label>Username or Email</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button class="button orange" type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register now</a></p>
        </div>
    </div>
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>