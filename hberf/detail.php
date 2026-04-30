<?php
require_once 'db.php';
$user = currentUser();
$id = intval($_GET['id'] ?? 0);
$ratingError = '';
$ratingSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating_submit'])) {
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $postedRecipeId = intval($_POST['recipe_id'] ?? 0);
    if ($postedRecipeId !== $id) {
        $ratingError = 'Invalid recipe selected for rating.';
    } else {
        $rating = intval($_POST['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            $ratingError = 'Please select at least 1 star.';
        } else {
            setRecipeRating($user['id'], $id, $rating);
            $ratingSuccess = 'Your rating has been saved.';
        }
    }
}

$stmt = $pdo->prepare('SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.id = ?');
$stmt->execute([$id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recipe) {
    header('Location: dashboard.php');
    exit;
}
$isFavourite = $user ? isFavourite($user['id'], $recipe['id']) : false;
$currentUserRating = $user ? getUserRating($user['id'], $recipe['id']) : null;
$ratingSummary = getRecipeRatingSummary($recipe['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - CookingBit</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="scripts.js" defer></script>
</head>

<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand"><a href="dashboard.php"><img src="logo.png" alt="CookingBit logo"></a></div>
            <div class="top-actions">
                <a class="button" href="dashboard.php">Back</a>
                <?php if ($user): ?>
                    <a class="button" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="button" href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="recipe-detail">
            <article class="detail-card">
                <div class="detail-image"
                    style="background-image:url('<?php echo htmlspecialchars($recipe['image_url'] ?: 'logo.png'); ?>');">
                </div>
                <div class="detail-body">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                        <?php if ($user): ?>
                            <button id="favourite-btn" class="favourite-btn <?php echo $isFavourite ? 'favourited' : ''; ?>"
                                onclick="toggleFavourite(<?php echo $recipe['id']; ?>, this, event)">
                                ♥
                            </button>
                        <?php endif; ?>
                    </div>
                    <p class="meta-chip"><?php echo htmlspecialchars($recipe['meal_type']); ?> ·
                        <?php echo htmlspecialchars($recipe['duration']); ?><?php if ($recipe['dietary_restriction']): ?>
                            · <?php echo htmlspecialchars($recipe['dietary_restriction']); ?><?php endif; ?>
                    </p>
                    <p class="small-text">Created by <?php echo htmlspecialchars($recipe['username']); ?></p>
                    <p class="detail-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
                    <div class="detail-section">
                        <h2>Ingredients</h2>
                        <ul>
                            <?php foreach (explode("\n", trim($recipe['ingredients'])) as $ingredient): ?>
                                <li><?php echo htmlspecialchars($ingredient); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="detail-section">
                        <h2>Instructions</h2>
                        <pre><?php echo htmlspecialchars($recipe['steps']); ?></pre>
                    </div>
                    <button class="button orange" onclick="downloadRecipePdf(<?php echo $recipe['id']; ?>)">Download
                        PDF</button>
                    <div class="detail-section rating-section">
                        <h2>Rate this recipe</h2>
                        <p class="small-text">
                            Average rating:
                            <?php if ($ratingSummary['rating_count'] > 0): ?>
                                <strong><?php echo number_format($ratingSummary['average_rating'], 1); ?>/5</strong>
                                (<?php echo $ratingSummary['rating_count']; ?>
                                <?php echo $ratingSummary['rating_count'] === 1 ? 'rating' : 'ratings'; ?>)
                            <?php else: ?>
                                No ratings yet
                            <?php endif; ?>
                        </p>

                        <?php if ($user): ?>
                            <?php if ($ratingSuccess): ?>
                                <div class="alert"><?php echo htmlspecialchars($ratingSuccess); ?></div>
                            <?php endif; ?>
                            <?php if ($ratingError): ?>
                                <div class="alert"><?php echo htmlspecialchars($ratingError); ?></div>
                            <?php endif; ?>
                            <form method="post" class="rating-form">
                                <input type="hidden" name="recipe_id" value="<?php echo (int) $recipe['id']; ?>">
                                <div class="star-input" role="radiogroup" aria-label="Recipe rating from 1 to 5 stars">
                                    <input type="radio" id="star5" name="rating" value="5" <?php echo $currentUserRating === 5 ? 'checked' : ''; ?> required>
                                    <label for="star5" title="5 stars">★</label>

                                    <input type="radio" id="star4" name="rating" value="4" <?php echo $currentUserRating === 4 ? 'checked' : ''; ?>>
                                    <label for="star4" title="4 stars">★</label>

                                    <input type="radio" id="star3" name="rating" value="3" <?php echo $currentUserRating === 3 ? 'checked' : ''; ?>>
                                    <label for="star3" title="3 stars">★</label>

                                    <input type="radio" id="star2" name="rating" value="2" <?php echo $currentUserRating === 2 ? 'checked' : ''; ?>>
                                    <label for="star2" title="2 stars">★</label>

                                    <input type="radio" id="star1" name="rating" value="1" <?php echo $currentUserRating === 1 ? 'checked' : ''; ?>>
                                    <label for="star1" title="1 star">★</label>
                                </div>
                                <button class="button" type="submit" name="rating_submit" value="1">Save rating</button>
                            </form>
                        <?php else: ?>
                            <p class="small-text">Please <a href="login.php">log in</a> to rate this recipe.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        </main>
    </div>
    <script>
        window.recipeData = {
            title: <?php echo json_encode($recipe['title']); ?>,
            description: <?php echo json_encode($recipe['description']); ?>,
            mealType: <?php echo json_encode($recipe['meal_type']); ?>,
            duration: <?php echo json_encode($recipe['duration']); ?>,
            dietaryRestriction: <?php echo json_encode($recipe['dietary_restriction']); ?>,
            ingredients: <?php echo json_encode(explode("\n", trim($recipe['ingredients']))); ?>,
            steps: <?php echo json_encode($recipe['steps']); ?>,
            author: <?php echo json_encode($recipe['username']); ?>
        };
    </script>
    <footer class="site-footer">
        <a href="about.php">About us</a>
        <span>&copy; 2026 - BitNova</span>
    </footer>
</body>

</html>