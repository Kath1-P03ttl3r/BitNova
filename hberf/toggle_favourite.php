<?php
require_once 'db.php';
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = intval($_POST['recipe_id'] ?? 0);
    $added = toggleFavourite($user['id'], $recipeId);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'isFavourite' => $added]);
    exit;
}

header('Location: dashboard.php');
exit;
?>