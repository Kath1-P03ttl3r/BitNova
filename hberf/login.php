<?php
require_once 'db.php';
$error = '';
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
            header('Location: dashboard.php');
            exit;
        }
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