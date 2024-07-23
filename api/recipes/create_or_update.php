<?php
require_once "../../config/config.php";
require_once "../utils/jwt.php";
require_once "../utils/response.php";
include '../utils/cors.php';
require "../utils/auth_middleware.php";

$decoded = authenticate_user_or_admin();
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->title) || !isset($data->description) || !isset($data->ingredients) || !isset($data->steps)) {
    send_response(null, 'All fields are required', 400);
    exit();
}

$user_id = $decoded['id'];
$title = $data->title;
$description = $data->description;
$ingredients = $data->ingredients;
$steps = $data->steps;

if (isset($data->id)) {
    // Update existing recipe
    $recipe_id = $data->id;

    // Check if the user owns the recipe
    $sql = "SELECT user_id FROM recipes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipe = $result->fetch_assoc();

    if ($recipe['user_id'] !== $user_id) {
        send_response(null, 'You are not authorized to update this recipe', 403);
        exit();
    }

    $sql = "UPDATE recipes SET title = ?, description = ?, ingredients = ?, steps = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $title, $description, $ingredients, $steps, $recipe_id);

    if ($stmt->execute()) {
        send_response(null, 'Recipe updated successfully', 200);
    } else {
        send_response(null, 'Failed to update recipe', 500);
    }
} else {
    // Add new recipe
    $sql = "INSERT INTO recipes (user_id, title, description, ingredients, steps, rating, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $user_id, $title, $description, $ingredients, $steps);

    if ($stmt->execute()) {
        send_response(null, 'Recipe added successfully', 200);
    } else {
        send_response(null, 'Failed to add recipe', 500);
    }
}
?>
