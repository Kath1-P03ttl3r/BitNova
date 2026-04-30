<?php
require_once 'db.php';
requireLogin();
$user = currentUser();

if (isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) ($_POST['delete_id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ? AND user_id = ?');
    $stmt->execute([$deleteId, $user['id']]);

    if ($stmt->rowCount() > 0) {
        $message = 'Recipe deleted successfully.';
    } else {
        $error = 'You can only delete recipes you created.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recipes - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand"><a href="dashboard.php"><img src="logo.png" alt="CookingBit logo"></a></div>
            <div class="top-actions">
                <a class="button" href="dashboard.php">Back</a>
                <a class="button icon-only-button logout-icon" href="logout.php" title="Log out"
                    aria-label="Log out">&#x21AA;</a>
            </div>
        </header>

        <main class="content-grid db-table-layout">
            <section class="table-panel">
                <div class="table-header-row"
                    style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
                    <h1>My Created Recipes</h1>
                    <a class="button orange" href="add_recipe.php">Create New Recipe</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($recipes): ?>
                    <div class="card-grid">
                        <?php foreach ($recipes as $recipe): ?>
                            <article class="recipe-card">
                                <a href="detail.php?id=<?php echo $recipe['id']; ?>">
                                    <div class="card-image"
                                        style="background-image:url('<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>');">
                                    </div>
                                </a>
                                <div class="card-body">
                                    <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
                                    <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                                    <div class="card-meta">
                                        <span><?php echo htmlspecialchars($recipe['meal_type']); ?></span>
                                        <span><?php echo htmlspecialchars($recipe['duration']); ?></span>
                                        <?php if ($recipe['dietary_restriction']): ?>
                                            <span class="tag"><?php echo htmlspecialchars($recipe['dietary_restriction']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="recipe-actions">
                                        <form method="get" action="edit_recipe.php" class="recipe-action-form">
                                            <input type="hidden" name="id" value="<?php echo (int) $recipe['id']; ?>">
                                            <button class="button" type="submit">Edit</button>
                                        </form>
                                        <form method="post" action="my_recipes.php" class="recipe-action-form"
                                            onsubmit="return confirm('Delete this recipe?');">
                                            <input type="hidden" name="delete_id" value="<?php echo (int) $recipe['id']; ?>">
                                            <button class="button" type="submit">Delete</button>
                                        </form>
                                    </div>
                                    <a class="link-button" href="detail.php?id=<?php echo $recipe['id']; ?>">Read more</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You have not created recipes yet.</p>
                        <a class="button orange" href="add_recipe.php">Add your first recipe</a>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>