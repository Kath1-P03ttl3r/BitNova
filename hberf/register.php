<?php
require_once 'db.php';
$error = '';
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please use a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'A user with this username or email already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users(username,email,password_hash,created_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
            $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'username' => $username];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="auth-shell">
        <div class="auth-card">
            <h1>Register</h1>
            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="register.php">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <label>Confirm Password</label>
                <input type="password" name="confirm" required>
                <button class="button orange" type="submit">Create Account</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>