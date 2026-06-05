<?php
// favourites page - shows recipes the user liked
// I will add more detailed beginner-style comments here so it's clear
// This page requires a logged-in user and shows only their favourite recipes
require_once 'db.php';
// ensure user is logged in before doing DB work
requireLogin();
$user = currentUser(); // small user array from session (id, username)

// read filter inputs from GET (these are optional)
$search = trim($_GET['search'] ?? ''); // free text search
$mealType = $_GET['meal_type'] ?? ''; // exact match filter
$duration = $_GET['duration'] ?? '';
$dietaryRestriction = $_GET['dietary_restriction'] ?? '';

// we'll build WHERE clauses in $conditions and values in $params
$conditions = [];
$params = [];
// if search text provided, match title, description or ingredients using LIKE
if ($search !== '') {
    $conditions[] = '(r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ?)';
    // use wildcards so partial matches work
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
// exact match filters for meal type and duration
if ($mealType !== '' && $mealType !== 'All') {
    $conditions[] = 'r.meal_type = ?';
    $params[] = $mealType;
}
if ($duration !== '' && $duration !== 'All') {
    $conditions[] = 'r.duration = ?';
    $params[] = $duration;
}
// dietary restriction is a simple select; in this DB it's stored as a comma-separated string
if ($dietaryRestriction !== '') {
    // exact equals is OK here because the UI provides values that match stored tokens
    $conditions[] = 'r.dietary_restriction = ?';
    $params[] = $dietaryRestriction;
}
// build final WHERE fragment; note we always filter by favourites.user_id
$where = '';
if ($conditions) {
    // prepend AND because the main query already has a WHERE for favourites
    $where = 'AND ' . implode(' AND ', $conditions);
}

// prepare the main query: join recipes to users and favourites
$stmt = $pdo->prepare("SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id JOIN favourites f ON f.recipe_id = r.id WHERE f.user_id = ? $where ORDER BY f.created_at DESC");
// merge the user id param at the front of the params array
$stmt->execute(array_merge([$user['id']], $params));
// fetch all matching recipes as associative arrays
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
                            <!-- recipe card: image, meta, and actions -->
                            <article class="recipe-card">
                                <!-- link to full recipe detail page -->
                                <a href="detail.php?id=<?php echo $recipe['id']; ?>">
                                    <div class="card-image"
                                        style="background-image:url('<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>');">
                                    </div>
                                </a>
                                <!-- favourite button: visually marked as favourited on this page -->
                                <!-- toggleFavourite will call `toggle_favourite.php` and update the button state -->
                                <button class="favourite-btn favourited"
                                    onclick="toggleFavourite(<?php echo $recipe['id']; ?>, this, event)">♥</button>
                                <div class="card-body">
                                    <!-- recipe title and short description; escaped to prevent XSS -->
                                    <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
                                    <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                                    <div class="card-meta">
                                        <!-- meta fields: meal type and duration shown as small tags -->
                                        <span><?php echo htmlspecialchars($recipe['meal_type']); ?></span>
                                        <span><?php echo htmlspecialchars($recipe['duration']); ?></span>
                                        <?php if ($recipe['dietary_restriction']): ?>
                                            <!-- dietary restriction is stored as a short string -->
                                            <span class="tag"><?php echo htmlspecialchars($recipe['dietary_restriction']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- link to see full recipe with ingredients and steps -->
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
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>