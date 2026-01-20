<?php
require "db.php";

$stmt = $pdo->query("
    SELECT r.recipe_id, r.recipe_name, r.recipe_description, r.portions, a.author_name
    FROM recipes r
    JOIN authors a ON r.author_id = a.author_id
");
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>