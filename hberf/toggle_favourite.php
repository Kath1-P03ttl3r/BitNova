<?php
// toggles favourite state for logged-in users
// This endpoint is small and intended to be called via AJAX (fetch) from the client.
require_once 'db.php';
requireLogin();
$user = currentUser();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recipe_id is expected from the client; cast to int to be safe
    $recipeId = intval($_POST['recipe_id'] ?? 0);
    // toggleFavourite returns true if added, false if removed
    $added = toggleFavourite($user['id'], $recipeId);

    // return a small JSON response so the UI can update
    // - success: whether the server processed the request (always true here)
    // - isFavourite: boolean representing final favourite state
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'isFavourite' => $added]);
    exit;
}

// non-POST access should be redirected to the dashboard
header('Location: dashboard.php');
exit;
?>