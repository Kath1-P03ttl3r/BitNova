<?php
require_once 'db.php';
$user = currentUser();
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.id = ?');
$stmt->execute([$id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recipe) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="scripts.js" defer></script>
</head>

<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand"><img src="logo.png" alt="CookingBit logo"></div>
            <div class="top-actions">
                <a class="button" href="dashboard.php">Back</a>
                <?php if ($user): ?>
                    <a class="button" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="button" href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="recipe-detail">
            <article class="detail-card">
                <div class="detail-image"
                    style="background-image:url('<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>');">
                </div>
                <div class="detail-body">
                    <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                    <p class="meta-chip"><?php echo htmlspecialchars($recipe['meal_type']); ?> ·
                        <?php echo htmlspecialchars($recipe['duration']); ?><?php if ($recipe['dietary_restriction']): ?>
                            · <?php echo htmlspecialchars($recipe['dietary_restriction']); ?><?php endif; ?>
                    </p>
                    <p class="small-text">Created by <?php echo htmlspecialchars($recipe['username']); ?></p>
                    <p class="detail-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
                    <div class="detail-section">
                        <h2>Ingredients</h2>
                        <ul>
                            <?php foreach (explode("\n", trim($recipe['ingredients'])) as $ingredient): ?>
                                <li><?php echo htmlspecialchars($ingredient); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="detail-section">
                        <h2>Instructions</h2>
                        <pre><?php echo htmlspecialchars($recipe['steps']); ?></pre>
                    </div>
                    <button class="button orange" onclick="downloadRecipePdf(<?php echo $recipe['id']; ?>)">Download
                        PDF</button>
                </div>
            </article>
        </main>
    </div>
    <script>
        window.recipeData = {
            title: <?php echo json_encode($recipe['title']); ?>,
            description: <?php echo json_encode($recipe['description']); ?>,
            mealType: <?php echo json_encode($recipe['meal_type']); ?>,
            duration: <?php echo json_encode($recipe['duration']); ?>,
            dietaryRestriction: <?php echo json_encode($recipe['dietary_restriction']); ?>,
            ingredients: <?php echo json_encode(explode("\n", trim($recipe['ingredients']))); ?>,
            steps: <?php echo json_encode($recipe['steps']); ?>,
            author: <?php echo json_encode($recipe['username']); ?>
        };
    </script>
</body>

</html>