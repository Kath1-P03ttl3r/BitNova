<?php
require_once 'db.php';
requireLogin();
$user = currentUser();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $mealType = $_POST['meal_type'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $dietaryRestrictionArray = $_POST['dietary_restriction'] ?? [];
    $dietaryRestriction = is_array($dietaryRestrictionArray) ? implode(', ', $dietaryRestrictionArray) : '';
    $imageUrl = trim($_POST['image_url'] ?? '');
    if ($title === '' || $description === '' || $ingredients === '' || $steps === '' || $mealType === '' || $duration === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO recipes(user_id,title,description,ingredients,steps,meal_type,duration,dietary_restriction,image_url,created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user['id'], $title, $description, $ingredients, $steps, $mealType, $duration, $dietaryRestriction, $imageUrl, date('Y-m-d H:i:s')]);
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe - CookingBit</title>
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
        <main class="content-form centered-form">
            <div class="form-card">
                <h1>Add a new recipe</h1>
                <?php if ($error): ?>
                    <div class="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post" action="add_recipe.php">
                    <label>Recipe Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                    <label>Description</label>
                    <textarea name="description" rows="3"
                        required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <label>Ingredients</label>
                    <textarea name="ingredients" rows="4" placeholder="One ingredient per line"
                        required><?php echo htmlspecialchars($ingredients ?? ''); ?></textarea>
                    <label>Steps</label>
                    <textarea name="steps" rows="5" placeholder="Use numbered steps or paragraphs"
                        required><?php echo htmlspecialchars($steps ?? ''); ?></textarea>
                    <div class="form-row">
                        <div>
                            <label>Meal Type</label>
                            <select name="meal_type" required>
                                <option value="">Select</option>
                                <option value="Breakfast" <?php echo ($mealType ?? '') === 'Breakfast' ? ' selected' : ''; ?>>Breakfast</option>
                                <option value="Lunch" <?php echo ($mealType ?? '') === 'Lunch' ? ' selected' : ''; ?>>
                                    Lunch</option>
                                <option value="Dinner" <?php echo ($mealType ?? '') === 'Dinner' ? ' selected' : ''; ?>>
                                    Dinner</option>
                                <option value="Snack" <?php echo ($mealType ?? '') === 'Snack' ? ' selected' : ''; ?>>
                                    Snack</option>
                                <option value="Dessert" <?php echo ($mealType ?? '') === 'Dessert' ? ' selected' : ''; ?>>
                                    Dessert</option>
                            </select>
                        </div>
                        <div>
                            <label>Duration</label>
                            <select name="duration" required>
                                <option value="">Select</option>
                                <option value="Under 15 Min" <?php echo ($duration ?? '') === 'Under 15 Min' ? ' selected' : ''; ?>>Under 15 Min</option>
                                <option value="15-30 Min" <?php echo ($duration ?? '') === '15-30 Min' ? ' selected' : ''; ?>>15-30 Min</option>
                                <option value="30-60 Min" <?php echo ($duration ?? '') === '30-60 Min' ? ' selected' : ''; ?>>30-60 Min</option>
                                <option value="Over 60 Min" <?php echo ($duration ?? '') === 'Over 60 Min' ? ' selected' : ''; ?>>Over 60 Min</option>
                            </select>
                        </div>
                    </div>
                    <label>Dietary restrictions</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="dietary_restriction[]" value="Vegan" <?php echo in_array('Vegan', explode(', ', $dietaryRestriction ?? '')) ? ' checked' : ''; ?>>
                            Vegan
                        </label>
                        <label>
                            <input type="checkbox" name="dietary_restriction[]" value="Vegetarian" <?php echo in_array('Vegetarian', explode(', ', $dietaryRestriction ?? '')) ? ' checked' : ''; ?>>
                            Vegetarian
                        </label>
                        <label>
                            <input type="checkbox" name="dietary_restriction[]" value="Gluten-free" <?php echo in_array('Gluten-free', explode(', ', $dietaryRestriction ?? '')) ? ' checked' : ''; ?>>
                            Gluten-free
                        </label>
                        <label>
                            <input type="checkbox" name="dietary_restriction[]" value="Halal" <?php echo in_array('Halal', explode(', ', $dietaryRestriction ?? '')) ? ' checked' : ''; ?>>
                            Halal
                        </label>
                        <label>
                            <input type="checkbox" name="dietary_restriction[]" value="Lactose-free" <?php echo in_array('Lactose-free', explode(', ', $dietaryRestriction ?? '')) ? ' checked' : ''; ?>>
                            Lactose-free
                        </label>
                    </div>
                    <label>Image URL</label>
                    <input type="url" name="image_url" placeholder="Optional image URL"
                        value="<?php echo htmlspecialchars($imageUrl ?? ''); ?>">
                    <button class="button orange" type="submit">Save Recipe</button>
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