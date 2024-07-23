<?php
require "../../config/config.php";
require "../utils/auth_middleware.php";
require "../utils/response.php";
include '../utils/cors.php';

$decoded = authenticate_user_or_admin();

if (!isset($_GET['id'])) {
    send_response(null, 'Recipe ID is required', 400);
    exit();
}

$recipe_id = $_GET['id'];
$user_id = $decoded['id'];

$sql = "SELECT recipes.id, recipes.user_id, recipes.title, recipes.description, recipes.ingredients, recipes.steps, recipes.rating, users.username as author
        FROM recipes
        JOIN users ON recipes.user_id = users.id
        WHERE recipes.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe) {
    send_response(null, 'Recipe not found', 404);
    exit();
}

$response = [
    'recipe' => $recipe,
    'user_id' => $user_id
];

send_response($response, 'Recipe fetched successfully', 200);
?>
