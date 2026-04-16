<?php
require_once 'db.php';
requireLogin();
$user = currentUser();

$search = trim($_GET['search'] ?? '');
$mealType = $_GET['meal_type'] ?? '';
$duration = $_GET['duration'] ?? '';
$dietaryRestriction = $_GET['dietary_restriction'] ?? '';
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
if ($dietaryRestriction !== '') {
    $conditions[] = 'r.dietary_restriction = ?';
    $params[] = $dietaryRestriction;
}
$where = '';
if ($conditions) {
    $where = 'AND ' . implode(' AND ', $conditions);
}
$stmt = $pdo->prepare("SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id JOIN favourites f ON f.recipe_id = r.id WHERE f.user_id = ? $where ORDER BY f.created_at DESC");
$stmt->execute(array_merge([$user['id']], $params));
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourites - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
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
        <main class="content-grid">
            <aside class="sidebar">
                <form method="get" action="favourites.php" class="filter-form">
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Search favourites...">
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
                            <a class="button" href="favourites.php" style="text-decoration: none;">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </aside>
            <section class="recipe-board">
                <h1>My Favourite Recipes</h1>
                <?php if ($recipes): ?>
                    <div class="card-grid">
                        <?php foreach ($recipes as $recipe): ?>
                            <article class="recipe-card">
                                <a href="detail.php?id=<?php echo $recipe['id']; ?>">
                                    <div class="card-image"
                                        style="background-image:url('<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>');">
                                    </div>
                                </a>
                                <button class="favourite-btn favourited"
                                    onclick="toggleFavourite(<?php echo $recipe['id']; ?>, this, event)">♥</button>
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
                        <p>You haven't favourited any recipes yet. Browse recipes and click the heart to add them here.</p>
                        <a class="button orange" href="dashboard.php">Browse Recipes</a>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>

</html>