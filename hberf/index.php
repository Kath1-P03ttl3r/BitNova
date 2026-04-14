<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CookingBit - Recipe Collection</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="topbar">
        <div class="brand">
            <a href="dashboard.php"><img src="logo.png" alt="CookingBit"></a>
        </div>
        <div></div>
    </div>

    <div class="landing-container">
        <div class="landing-card">
            <h1>Welcome to CookingBit</h1>
            <p>Discover amazing recipes and share your culinary creations with our community.</p>

            <div class="landing-buttons">
                <a href="login.php" class="button orange">Login</a>
                <a href="register.php" class="button orange">Register</a>
                <a href="dashboard.php" class="button" style="background: #d97528; color: white;">Browse as Guest</a>
            </div>
        </div>
    </div>
</body>

</html>