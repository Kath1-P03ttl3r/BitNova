<?php
require_once 'db.php';
$user = currentUser();
$showMyRecipesButton = false;
if ($user && !isAdmin()) {
    $ownedRecipeCountStmt = $pdo->prepare('SELECT COUNT(*) FROM recipes WHERE user_id = ?');
    $ownedRecipeCountStmt->execute([$user['id']]);
    $showMyRecipesButton = ((int) $ownedRecipeCountStmt->fetchColumn()) > 0;
}

$search = trim($_GET['search'] ?? '');
$mealType = $_GET['meal_type'] ?? '';
$duration = $_GET['duration'] ?? '';
$dietaryRestrictions = $_GET['dietary_restriction'] ?? [];
if (!is_array($dietaryRestrictions)) {
    $dietaryRestrictions = [];
}
$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($mealType !== '' && $mealType !== 'All') {
    $conditions[] = 'r.meal_type = ?';
    $params[] = $mealType;
}
if ($duration !== '' && $duration !== 'All') {
    $conditions[] = 'r.duration = ?';
    $params[] = $duration;
}
if (!empty($dietaryRestrictions)) {
    $restrictionConditions = [];
    foreach ($dietaryRestrictions as $restriction) {
        $restrictionConditions[] = 'r.dietary_restriction LIKE ?';
        $params[] = "%$restriction%";
    }
    $conditions[] = '(' . implode(' OR ', $restrictionConditions) . ')';
}
$where = '';
if ($conditions) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}
$stmt = $pdo->prepare("SELECT r.*, COALESCE(AVG(rr.rating), 0) AS average_rating, COUNT(rr.id) AS rating_count FROM recipes r LEFT JOIN recipe_ratings rr ON rr.recipe_id = r.id $where GROUP BY r.id ORDER BY r.created_at DESC");
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recipes - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>

<body class="dashboard-shell">
    <div class="page-shell">
        <header class="topbar">
            <div class="brand"><a href="dashboard.php"><img src="logo.png" alt="CookingBit logo"></a></div>
            <div class="top-actions">
                <?php if ($user): ?>
                    <?php if ($showMyRecipesButton): ?>
                        <a class="button" href="my_recipes.php">My Recipes</a>
                    <?php endif; ?>
                    <a class="button" href="add_recipe.php">Add Recipe</a>
                    <?php if (!isAdmin()): ?>
                        <a class="button icon-only-button" href="favourites.php" title="My Favourites"
                            aria-label="My Favourites">&hearts;</a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <a class="button" href="db_table.php">DB Table</a>
                    <?php endif; ?>
                    <a class="button icon-only-button logout-icon" href="logout.php" title="Log out"
                        aria-label="Log out">&#x21AA;</a>
                <?php else: ?>
                    <a class="button" href="login.php">Login</a>
                    <a class="button orange" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="content-grid">
            <aside class="sidebar">
                <form method="get" action="dashboard.php" class="filter-form">
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Search recipes...">
                    </div>
                    <div class="filter-group">
                        <label>Meal Type</label>
                        <select name="meal_type">
                            <option value="All">All</option>
                            <option value="Breakfast" <?php echo $mealType === 'Breakfast' ? ' selected' : ''; ?>>
                                Breakfast</option>
                            <option value="Lunch" <?php echo $mealType === 'Lunch' ? ' selected' : ''; ?>>Lunch</option>
                            <option value="Dinner" <?php echo $mealType === 'Dinner' ? ' selected' : ''; ?>>Dinner
                            </option>
                            <option value="Snack" <?php echo $mealType === 'Snack' ? ' selected' : ''; ?>>Snack</option>
                            <option value="Dessert" <?php echo $mealType === 'Dessert' ? ' selected' : ''; ?>>Dessert
                            </option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Duration</label>
                        <select name="duration">
                            <option value="All">All</option>
                            <option value="Under 15 Min" <?php echo $duration === 'Under 15 Min' ? ' selected' : ''; ?>>
                                Under 15 Min</option>
                            <option value="15-30 Min" <?php echo $duration === '15-30 Min' ? ' selected' : ''; ?>>15-30
                                Min</option>
                            <option value="30-60 Min" <?php echo $duration === '30-60 Min' ? ' selected' : ''; ?>>30-60
                                Min</option>
                            <option value="Over 60 Min" <?php echo $duration === 'Over 60 Min' ? ' selected' : ''; ?>>Over
                                60 Min</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Dietary restrictions</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="dietary_restriction[]" value="Vegan" <?php echo in_array('Vegan', $dietaryRestrictions) ? ' checked' : ''; ?>>
                                Vegan
                            </label>
                            <label>
                                <input type="checkbox" name="dietary_restriction[]" value="Vegetarian" <?php echo in_array('Vegetarian', $dietaryRestrictions) ? ' checked' : ''; ?>>
                                Vegetarian
                            </label>
                            <label>
                                <input type="checkbox" name="dietary_restriction[]" value="Gluten-free" <?php echo in_array('Gluten-free', $dietaryRestrictions) ? ' checked' : ''; ?>>
                                Gluten-free
                            </label>
                            <label>
                                <input type="checkbox" name="dietary_restriction[]" value="Halal" <?php echo in_array('Halal', $dietaryRestrictions) ? ' checked' : ''; ?>>
                                Halal
                            </label>
                            <label>
                                <input type="checkbox" name="dietary_restriction[]" value="Lactose-free" <?php echo in_array('Lactose-free', $dietaryRestrictions) ? ' checked' : ''; ?>>
                                Lactose-free
                            </label>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button class="button orange" type="submit">Filter</button>
                        <?php
                        $hasActiveFilter = $search !== '' || ($mealType !== '' && $mealType !== 'All') || ($duration !== '' && $duration !== 'All') || !empty($dietaryRestrictions);
                        if ($hasActiveFilter):
                            ?>
                            <a class="button" href="dashboard.php" style="text-decoration: none;">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </aside>
            <section class="recipe-board">
                <?php if ($recipes): ?>
                    <div class="card-grid">
                        <?php foreach ($recipes as $recipe): ?>
                            <?php $isFavourite = $user ? isFavourite($user['id'], $recipe['id']) : false; ?>
                            <article class="recipe-card">
                                <a href="detail.php?id=<?php echo $recipe['id']; ?>">
                                    <div class="card-image"
                                        style="background-image:url('<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>');">
                                    </div>
                                </a>
                                <?php if ($user): ?>
                                    <button class="favourite-btn <?php echo $isFavourite ? 'favourited' : ''; ?>"
                                        onclick="toggleFavourite(<?php echo $recipe['id']; ?>, this, event)">♥</button>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
                                    <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                                    <div class="card-meta">
                                        <span><?php echo htmlspecialchars($recipe['meal_type']); ?></span>
                                        <span><?php echo htmlspecialchars($recipe['duration']); ?></span>
                                        <?php if ($recipe['dietary_restriction']): ?><span
                                                class="tag"><?php echo htmlspecialchars($recipe['dietary_restriction']); ?></span><?php endif; ?>
                                    </div>
                                    <p class="rating-meta">
                                        <?php if ((int) $recipe['rating_count'] > 0): ?>
                                            ★ <?php echo number_format((float) $recipe['average_rating'], 1); ?>/5
                                            (<?php echo (int) $recipe['rating_count']; ?>)
                                        <?php else: ?>
                                            ★ Not rated yet
                                        <?php endif; ?>
                                    </p>
                                    <a class="link-button" href="detail.php?id=<?php echo $recipe['id']; ?>">Read more</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No recipes match your filters yet. Add a new recipe or adjust the search.</p>
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