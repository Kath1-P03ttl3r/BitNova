<?php
require "db.php";

$id = $_GET["id"] ?? 0;

$stmt = $pdo->prepare("
    SELECT r.recipe_name, r.recipe_description, r.portions, a.author_name
    FROM recipes r
    JOIN authors a ON r.author_id = a.author_id
    WHERE r.recipe_id = ?
");
$stmt->execute([$id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

$ingredientsStmt = $pdo->prepare("
    SELECT i.ingredient_name, ri.amount, ri.unit
    FROM recipes_ingredients ri
    JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
    WHERE ri.recipe_id = ?
");
$ingredientsStmt->execute([$id]);
$ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

$stepsStmt = $pdo->prepare("
    SELECT step_number, step_description
    FROM steps
    WHERE recipe_id = ?
    ORDER BY step_number
");
$stepsStmt->execute([$id]);
$steps = $stepsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($recipe["recipe_name"]) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<a href="index.php">← Zurück</a>

<h1><?= htmlspecialchars($recipe["recipe_name"]) ?></h1>
<p class="author">Von: <?= htmlspecialchars($recipe["author_name"]) ?></p>
<p><?= htmlspecialchars($recipe["recipe_description"]) ?></p>
<p><strong>Portionen:</strong> <?= $recipe["portions"] ?></p>

<h2>Zutaten</h2>
<ul>
<?php foreach ($ingredients as $ing): ?>
    <li><?= htmlspecialchars($ing["amount"] . " " . $ing["unit"] . " " . $ing["ingredient_name"]) ?></li>
<?php endforeach; ?>
</ul>

<h2>Zubereitung</h2>
<ol>
<?php foreach ($steps as $step): ?>
    <li><?= htmlspecialchars($step["step_description"]) ?></li>
<?php endforeach; ?>
</ol>

</body>
</html>
