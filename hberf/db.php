<?php
session_start();
$dbFile = __DIR__ . '/data/cookingbit.db';
if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0755, true);
}
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL
)");

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

$existingColumns = $pdo->query("PRAGMA table_info(recipes)")->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_column($existingColumns, 'name');
if (!in_array('dietary_restriction', $columnNames, true)) {
    $pdo->exec('ALTER TABLE recipes ADD COLUMN dietary_restriction TEXT NOT NULL DEFAULT ""');
}

$sampleCheck = $pdo->query('SELECT COUNT(*) FROM recipes')->fetchColumn();
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
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function isAdmin()
{
    $user = currentUser();
    return $user && $user['username'] === 'BitNova';
}

function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}
