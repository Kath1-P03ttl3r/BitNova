<?php
// little DB setup, I read about sqlite and tried to make it work
session_start();
$dbFile = __DIR__ . '/data/cookingbit.db';
// create folder for DB if it doesn't exist, I guess
if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0755, true);
}
// connect to sqlite (this was easiest for me)
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

// users table, stores people who can login (simple fields)
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL
)");

// recipes table, basic recipe info (not fancy)
$pdo->exec("CREATE TABLE IF NOT EXISTS recipes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    ingredients TEXT NOT NULL,
    steps TEXT NOT NULL,
    meal_type TEXT NOT NULL,
    duration TEXT NOT NULL,
    dietary_restriction TEXT NOT NULL DEFAULT '',
    image_url TEXT,
    created_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// favourites table, links users to recipes they like
$pdo->exec("CREATE TABLE IF NOT EXISTS favourites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    recipe_id INTEGER NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE(user_id, recipe_id)
)");

// recipe_ratings stores 1-5 star ratings per user
$pdo->exec("CREATE TABLE IF NOT EXISTS recipe_ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    recipe_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE(user_id, recipe_id)
)");

$existingColumns = $pdo->query("PRAGMA table_info(recipes)")->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_column($existingColumns, 'name');
if (!in_array('dietary_restriction', $columnNames, true)) {
    $pdo->exec('ALTER TABLE recipes ADD COLUMN dietary_restriction TEXT NOT NULL DEFAULT ""');
}

$sampleCheck = $pdo->query('SELECT COUNT(*) FROM recipes')->fetchColumn();
// if no recipes exist yet we add a couple so the UI shows something
if ($sampleCheck == 0) {
    $now = date('Y-m-d H:i:s');
    $sampleRecipes = [
        [
            'title' => 'Healthy Lunch Bowl',
            'description' => 'Fresh ingredients for a balanced lunch.',
            'ingredients' => "Avocado\nCherry tomatoes\nChickpeas\nSpinach\nBrown rice\nOlive oil\nLemon juice",
            'steps' => "1. Cook rice.\n2. Chop vegetables.\n3. Mix everything and season.",
            'meal_type' => 'Lunch',
            'duration' => '15-30 Min',
            'dietary_restriction' => 'Vegan',
            'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80',
            'user_id' => 1,
        ],
        [
            'title' => 'Quick Dinner Pasta',
            'description' => 'A fast pasta dish with bright tomato sauce.',
            'ingredients' => "Pasta\nTomatoes\nGarlic\nOlive oil\nBasil\nParmesan",
            'steps' => "1. Cook pasta.\n2. Sauté garlic and tomatoes.\n3. Toss with pasta.",
            'meal_type' => 'Dinner',
            'duration' => '30-60 Min',
            'dietary_restriction' => '',
            'image_url' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=80',
            'user_id' => 1,
        ],
    ];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO users(username,email,password_hash,created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute(['guest', 'guest@example.com', password_hash('guest123', PASSWORD_DEFAULT), $now]);
        $userId = $pdo->lastInsertId();
        if (!$userId) {
            $userId = $pdo->query('SELECT id FROM users WHERE username = "guest"')->fetchColumn();
        }
        $stmt = $pdo->prepare('INSERT INTO recipes(user_id, title, description, ingredients, steps, meal_type, duration, dietary_restriction, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($sampleRecipes as $recipe) {
            $stmt->execute([
                $userId,
                $recipe['title'],
                $recipe['description'],
                $recipe['ingredients'],
                $recipe['steps'],
                $recipe['meal_type'],
                $recipe['duration'],
                $recipe['dietary_restriction'],
                $recipe['image_url'],
                $now,
            ]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

$adminHash = password_hash('CookingBit', PASSWORD_DEFAULT);
// ensure an admin user exists (simple legacy handling)
$adminUser = $pdo->query('SELECT id, username FROM users WHERE username = "BitNova"')->fetch(PDO::FETCH_ASSOC);
$legacyAdminUser = $pdo->query('SELECT id FROM users WHERE username = "admin"')->fetch(PDO::FETCH_ASSOC);
if ($adminUser) {
    $stmt = $pdo->prepare('UPDATE users SET email = ?, password_hash = ? WHERE id = ?');
    $stmt->execute(['admin@example.com', $adminHash, $adminUser['id']]);
} elseif ($legacyAdminUser) {
    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?');
    $stmt->execute(['BitNova', 'admin@example.com', $adminHash, $legacyAdminUser['id']]);
} else {
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO users(username,email,password_hash,created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute(['BitNova', 'admin@example.com', $adminHash, date('Y-m-d H:i:s')]);
}

function requireLogin()
{
    /**
     * requireLogin
     * Ensures a user is logged in by checking the session.
     * If not logged in it redirects to the login page.
     * I kept this simple on purpose.
     */
    // redirect to login if not authenticated
    // this is where we check session for a user and bail out if missing
    if (empty($_SESSION['user'])) {
        // no user found in session -> send them to login page
        header('Location: login.php');
        // stop executing so unauthenticated users don't run protected code
        exit;
    }
}

function currentUser()
{
    /**
     * currentUser
     * Returns the current user array from the session or null if none.
     * Useful for templates to show/hide UI parts.
     */
    // return user info from session or null
    // we store only minimal data in the session (id and username)
    // templates can use this to decide what to show
    return $_SESSION['user'] ?? null;
}

function isAdmin()
{
    /**
     * isAdmin
     * Very simple admin check: true if username equals BitNova.
     * In a real app you'd check roles or permissions.
     */
    // very basic admin check by username
    // this is intentionally simple for the demo app
    // a real app would check a role or permission table
    $user = currentUser();
    // if user is not logged in this will return false
    return $user && $user['username'] === 'BitNova';
}

function requireAdmin()
{
    /**
     * requireAdmin
     * Redirects users who are not admin away from admin pages.
     */
    // block access if not admin
    // redirect non-admins to a safe page
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

function isFavourite($userId, $recipeId)
{
    /**
     * isFavourite
     * Checks if a given user has favourited a recipe.
     * Returns boolean.
     */
    // returns true if user already favourited the recipe
    global $pdo;
    // prepare query with placeholders to avoid injection
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM favourites WHERE user_id = ? AND recipe_id = ?');
    $stmt->execute([$userId, $recipeId]);
    // fetchColumn returns the count (0 or more)
    return $stmt->fetchColumn() > 0;
}

function toggleFavourite($userId, $recipeId)
{
    /**
     * toggleFavourite
     * Adds a favourite row if missing, or removes it if present.
     * Returns the new boolean state (true if favourited now).
     */
    // toggle favourite for a user and return new state
    global $pdo;
    // If a favourite already exists, remove it (toggle off)
    if (isFavourite($userId, $recipeId)) {
        $stmt = $pdo->prepare('DELETE FROM favourites WHERE user_id = ? AND recipe_id = ?');
        $stmt->execute([$userId, $recipeId]);
        // returning false signals the UI the recipe is no longer favourited
        return false; // removed
    } else {
        // otherwise insert a new favourite row with the current time
        $stmt = $pdo->prepare('INSERT INTO favourites(user_id, recipe_id, created_at) VALUES (?, ?, ?)');
        // use the explicit $userId parameter here (avoid undefined $user variable)
        $stmt->execute([$userId, $recipeId, date('Y-m-d H:i:s')]);
        // returning true signals the UI the recipe is now favourited
        return true; // added
    }
}

function getUserRating($userId, $recipeId)
{
    /**
     * getUserRating
     * Returns the integer rating by a user for a given recipe, or null.
     */
    // return rating given by user for a recipe or null
    global $pdo;
    // query the recipe_ratings table for this user and recipe
    $stmt = $pdo->prepare('SELECT rating FROM recipe_ratings WHERE user_id = ? AND recipe_id = ?');
    $stmt->execute([$userId, $recipeId]);
    $rating = $stmt->fetchColumn();
    // fetchColumn returns false when no row exists; convert to null otherwise an int
    return $rating === false ? null : (int) $rating;
}

function setRecipeRating($userId, $recipeId, $rating)
{
    /**
     * setRecipeRating
     * Upserts a user's rating for a recipe. Uses SQL upsert to keep it simple.
     */
    // insert or update user's rating using SQLite upsert
    global $pdo;
    // set timestamps (created_at/updated_at) for tracking
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO recipe_ratings(user_id, recipe_id, rating, created_at, updated_at) VALUES (?, ?, ?, ?, ?) ON CONFLICT(user_id, recipe_id) DO UPDATE SET rating = excluded.rating, updated_at = excluded.updated_at');
    // execute the upsert: on conflict update the rating and updated_at
    $stmt->execute([$userId, $recipeId, $rating, $now, $now]);
}

function getRecipeRatingSummary($recipeId)
{
    /**
     * getRecipeRatingSummary
     * Returns an array with 'rating_count' and 'average_rating'.
     * Good for displaying summary data without multiple queries.
     */
    // returns count and average for a recipe's ratings
    global $pdo;
    // aggregate query to get count and average in one call
    $stmt = $pdo->prepare('SELECT COUNT(*) AS rating_count, COALESCE(AVG(rating), 0) AS average_rating FROM recipe_ratings WHERE recipe_id = ?');
    $stmt->execute([$recipeId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // normalize types for easier consumption by templates
    return [
        'rating_count' => (int) ($row['rating_count'] ?? 0),
        'average_rating' => (float) ($row['average_rating'] ?? 0),
    ];
}
