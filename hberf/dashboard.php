<?php
require_once 'db.php';
$user = currentUser();
$search = trim($_GET['search'] ?? '');
$mealType = $_GET['meal_type'] ?? '';
$duration = $_GET['duration'] ?? '';
$dietaryRestriction = $_GET['dietary_restriction'] ?? '';
$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(title LIKE ? OR description LIKE ? OR ingredients LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($mealType !== '' && $mealType !== 'All') {
    $conditions[] = 'meal_type = ?';
    $params[] = $mealType;
}
if ($duration !== '' && $duration !== 'All') {
    $conditions[] = 'duration = ?';
    $params[] = $duration;
}
if ($dietaryRestriction !== '') {
    $conditions[] = 'dietary_restriction = ?';
    $params[] = $dietaryRestriction;
}
$where = '';
if ($conditions) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}
$stmt = $pdo->prepare("SELECT * FROM recipes $where ORDER BY created_at DESC");
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
</head>

<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand"><img src="logo.png" alt="CookingBit logo"></div>
            <div class="top-actions">
                <?php if ($user): ?>
                    <span>Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <a class="button" href="add_recipe.php">Add Recipe</a>
                    <?php if (isAdmin()): ?>
                        <a class="button" href="db_table.php">DB Table</a>
                    <?php endif; ?>
                    <a class="button" href="logout.php">Logout</a>
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
                        <select name="dietary_restriction">
                            <option value="" <?php echo $dietaryRestriction === '' ? ' selected' : ''; ?>>All</option>
                            <option value="Vegan" <?php echo $dietaryRestriction === 'Vegan' ? ' selected' : ''; ?>>Vegan
                            </option>
                            <option value="Vegetarian" <?php echo $dietaryRestriction === 'Vegetarian' ? ' selected' : ''; ?>>Vegetarian</option>
                            <option value="Gluten-free" <?php echo $dietaryRestriction === 'Gluten-free' ? ' selected' : ''; ?>>Gluten-free</option>
                            <option value="Halal" <?php echo $dietaryRestriction === 'Halal' ? ' selected' : ''; ?>>Halal
                            </option>
                            <option value="Lactose-free" <?php echo $dietaryRestriction === 'Lactose-free' ? ' selected' : ''; ?>>Lactose-free</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button class="button orange" type="submit">Filter</button>
                        <?php
                        $hasActiveFilter = $search !== '' || ($mealType !== '' && $mealType !== 'All') || ($duration !== '' && $duration !== 'All') || $dietaryRestriction !== '';
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
                                        <?php if ($recipe['dietary_restriction']): ?><span
                                                class="tag"><?php echo htmlspecialchars($recipe['dietary_restriction']); ?></span><?php endif; ?>
                                    </div>
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
</body>

</html>