<?php
require_once 'db.php';
requireLogin();
$user = currentUser();

if (isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header('Location: my_recipes.php');
    exit;
}

$error = '';
$title = $recipe['title'];
$description = $recipe['description'];
$ingredients = $recipe['ingredients'];
$steps = $recipe['steps'];
$mealType = $recipe['meal_type'];
$duration = $recipe['duration'];
$dietaryRestriction = $recipe['dietary_restriction'];
$imageUrl = $recipe['image_url'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $mealType = $_POST['meal_type'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $dietaryRestriction = $_POST['dietary_restriction'] ?? '';
    $imageUrl = trim($_POST['image_url'] ?? '');

    if ($title === '' || $description === '' || $ingredients === '' || $steps === '' || $mealType === '' || $duration === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare('UPDATE recipes SET title = ?, description = ?, ingredients = ?, steps = ?, meal_type = ?, duration = ?, dietary_restriction = ?, image_url = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([
            $title,
            $description,
            $ingredients,
            $steps,
            $mealType,
            $duration,
            $dietaryRestriction,
            $imageUrl,
            $id,
            $user['id'],
        ]);

        header('Location: my_recipes.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand"><a href="dashboard.php"><img src="logo.png" alt="CookingBit logo"></a></div>
            <div class="top-actions">
                <a class="button" href="my_recipes.php">Back to My Recipes</a>
                <a class="button icon-only-button logout-icon" href="logout.php" title="Log out"
                    aria-label="Log out">&#x21AA;</a>
            </div>
        </header>

        <main class="content-form">
            <div class="form-card">
                <h1>Edit your recipe</h1>
                <?php if ($error): ?>
                    <div class="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="edit_recipe.php?id=<?php echo $id; ?>">
                    <label>Recipe Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

                    <label>Description</label>
                    <textarea name="description" rows="3"
                        required><?php echo htmlspecialchars($description); ?></textarea>

                    <label>Ingredients</label>
                    <textarea name="ingredients" rows="4" placeholder="One ingredient per line"
                        required><?php echo htmlspecialchars($ingredients); ?></textarea>

                    <label>Steps</label>
                    <textarea name="steps" rows="5" placeholder="Use numbered steps or paragraphs"
                        required><?php echo htmlspecialchars($steps); ?></textarea>

                    <div class="form-row">
                        <div>
                            <label>Meal Type</label>
                            <select name="meal_type" required>
                                <option value="">Select</option>
                                <option value="Breakfast" <?php echo $mealType === 'Breakfast' ? ' selected' : ''; ?>>
                                    Breakfast</option>
                                <option value="Lunch" <?php echo $mealType === 'Lunch' ? ' selected' : ''; ?>>Lunch
                                </option>
                                <option value="Dinner" <?php echo $mealType === 'Dinner' ? ' selected' : ''; ?>>Dinner
                                </option>
                                <option value="Snack" <?php echo $mealType === 'Snack' ? ' selected' : ''; ?>>Snack
                                </option>
                            </select>
                        </div>
                        <div>
                            <label>Duration</label>
                            <select name="duration" required>
                                <option value="">Select</option>
                                <option value="Under 15 Min" <?php echo $duration === 'Under 15 Min' ? ' selected' : ''; ?>>Under 15 Min</option>
                                <option value="15-30 Min" <?php echo $duration === '15-30 Min' ? ' selected' : ''; ?>>
                                    15-30 Min</option>
                                <option value="30-60 Min" <?php echo $duration === '30-60 Min' ? ' selected' : ''; ?>>
                                    30-60 Min</option>
                                <option value="Over 60 Min" <?php echo $duration === 'Over 60 Min' ? ' selected' : ''; ?>>
                                    Over 60 Min</option>
                            </select>
                        </div>
                    </div>

                    <label>Dietary restrictions</label>
                    <select name="dietary_restriction">
                        <option value="" <?php echo $dietaryRestriction === '' ? ' selected' : ''; ?>>All</option>
                        <option value="Vegan" <?php echo $dietaryRestriction === 'Vegan' ? ' selected' : ''; ?>>Vegan
                        </option>
                        <option value="Vegetarian" <?php echo $dietaryRestriction === 'Vegetarian' ? ' selected' : ''; ?>>
                            Vegetarian</option>
                        <option value="Gluten-free" <?php echo $dietaryRestriction === 'Gluten-free' ? ' selected' : ''; ?>>Gluten-free</option>
                        <option value="Halal" <?php echo $dietaryRestriction === 'Halal' ? ' selected' : ''; ?>>Halal
                        </option>
                        <option value="Lactose-free" <?php echo $dietaryRestriction === 'Lactose-free' ? ' selected' : ''; ?>>Lactose-free</option>
                    </select>

                    <label>Image URL</label>
                    <input type="url" name="image_url" placeholder="Optional image URL"
                        value="<?php echo htmlspecialchars($imageUrl); ?>">

                    <button class="button orange" type="submit">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>