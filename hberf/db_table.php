<?php
require_once 'db.php';
$user = currentUser();
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$guestId = $pdo->query('SELECT id FROM users WHERE username = "guest"')->fetchColumn();
if (!$guestId) {
    $stmt = $pdo->prepare('INSERT INTO users(username,email,password_hash,created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute(['guest', 'guest@example.com', password_hash('guest123', PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
    $guestId = $pdo->lastInsertId();
}

$errors = [];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = (int) $_POST['delete_id'];
        $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ?');
        $stmt->execute([$deleteId]);
        $message = 'Rezept wurde gelöscht.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $steps = trim($_POST['steps'] ?? '');
        $mealType = trim($_POST['meal_type'] ?? 'Lunch');
        $duration = trim($_POST['duration'] ?? '15-30 Min');
        $dietaryRestriction = trim($_POST['dietary_restriction'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($title === '' || $description === '' || $ingredients === '' || $steps === '') {
            $errors[] = 'Please fill in title, description, ingredients and steps.';
        }

        if (empty($errors)) {
            $authorId = $user['id'] ?? $guestId;
            $stmt = $pdo->prepare('INSERT INTO recipes(user_id, title, description, ingredients, steps, meal_type, duration, dietary_restriction, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $authorId,
                $title,
                $description,
                $ingredients,
                $steps,
                $mealType,
                $duration,
                $dietaryRestriction,
                $imageUrl,
                date('Y-m-d H:i:s'),
            ]);
            $message = 'Neue Rezeptzeile wurde erfolgreich hinzugefügt.';
        }
    }
}

$recipes = $pdo->query('SELECT r.*, u.username AS author FROM recipes r JOIN users u ON r.user_id = u.id ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Table - CookingBit</title>
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
                    <h1>Recipe Database</h1>
                    <a class="button orange" href="add_recipe.php">Add Recipe</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="alert">
                        <?php echo htmlspecialchars(implode(' ', $errors)); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Author</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Ingredients</th>
                                <th>Steps</th>
                                <th>Meal Type</th>
                                <th>Duration</th>
                                <th>Dietary Restriction</th>
                                <th>Image</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipes as $recipe): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recipe['id']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['author']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['title']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($recipe['ingredients'])); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($recipe['steps'])); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['meal_type']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['duration']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['dietary_restriction']); ?></td>
                                    <td>
                                        <img class="db-table-image"
                                            src="<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>"
                                            alt="Recipe image">
                                    </td>
                                    <td><?php echo htmlspecialchars($recipe['created_at']); ?></td>
                                    <td>
                                        <form method="post" action="db_table.php"
                                            onsubmit="return confirm('Bist du sicher, dass du dieses Rezept löschen möchtest?');">
                                            <input type="hidden" name="delete_id"
                                                value="<?php echo htmlspecialchars($recipe['id']); ?>">
                                            <button class="button" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="db-form" style="margin-top: 32px;">
                    <h2>Add new recipe directly</h2>
                    <form method="post" action="db_table.php">
                        <label>Title</label>
                        <input type="text" name="title" required
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">

                        <label>Description</label>
                        <textarea name="description"
                            required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                        <label>Ingredients</label>
                        <textarea name="ingredients"
                            required><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>

                        <label>Steps</label>
                        <textarea name="steps"
                            required><?php echo htmlspecialchars($_POST['steps'] ?? ''); ?></textarea>

                        <label>Meal Type</label>
                        <select name="meal_type">
                            <option value="All" <?php echo (($_POST['meal_type'] ?? 'All') === 'All') ? 'selected' : ''; ?>>All</option>
                            <option value="Breakfast" <?php echo (($_POST['meal_type'] ?? '') === 'Breakfast') ? 'selected' : ''; ?>>Breakfast</option>
                            <option value="Lunch" <?php echo (($_POST['meal_type'] ?? '') === 'Lunch') ? 'selected' : ''; ?>>Lunch</option>
                            <option value="Dinner" <?php echo (($_POST['meal_type'] ?? '') === 'Dinner') ? 'selected' : ''; ?>>Dinner</option>
                            <option value="Snack" <?php echo (($_POST['meal_type'] ?? '') === 'Snack') ? 'selected' : ''; ?>>Snack</option>
                        </select>

                        <label>Duration</label>
                        <select name="duration">
                            <option value="All" <?php echo (($_POST['duration'] ?? 'All') === 'All') ? 'selected' : ''; ?>>All</option>
                            <option value="Under 15 Min" <?php echo (($_POST['duration'] ?? '') === 'Under 15 Min') ? 'selected' : ''; ?>>Under 15 Min</option>
                            <option value="15-30 Min" <?php echo (($_POST['duration'] ?? '') === '15-30 Min') ? 'selected' : ''; ?>>15-30 Min</option>
                            <option value="30-60 Min" <?php echo (($_POST['duration'] ?? '') === '30-60 Min') ? 'selected' : ''; ?>>30-60 Min</option>
                            <option value="Over 60 Min" <?php echo (($_POST['duration'] ?? '') === 'Over 60 Min') ? 'selected' : ''; ?>>Over 60 Min</option>
                        </select>

                        <label>Dietary Restriction</label>
                        <select name="dietary_restriction">
                            <option value="" <?php echo (($_POST['dietary_restriction'] ?? '') === '') ? 'selected' : ''; ?>>All</option>
                            <option value="Vegan" <?php echo (($_POST['dietary_restriction'] ?? '') === 'Vegan') ? 'selected' : ''; ?>>Vegan</option>
                            <option value="Vegetarian" <?php echo (($_POST['dietary_restriction'] ?? '') === 'Vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                            <option value="Gluten-free" <?php echo (($_POST['dietary_restriction'] ?? '') === 'Gluten-free') ? 'selected' : ''; ?>>Gluten-free</option>
                            <option value="Halal" <?php echo (($_POST['dietary_restriction'] ?? '') === 'Halal') ? 'selected' : ''; ?>>Halal</option>
                            <option value="Lactose-free" <?php echo (($_POST['dietary_restriction'] ?? '') === 'Lactose-free') ? 'selected' : ''; ?>>Lactose-free</option>
                        </select>

                        <label>Image URL</label>
                        <input type="url" name="image_url"
                            value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>">

                        <div class="form-actions" style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px;">
                            <button class="button orange" type="submit">Add row</button>
                            <a class="button" href="db_table.php">Clear</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
    </form>
    </aside>
    </main>
    </div>
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>