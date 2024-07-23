<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";
include '../utils/cors.php';
require "../utils/auth_middleware.php";

$decoded = authenticate_user_or_admin();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->recipe_id) || !isset($data->comment)) {
    send_response(null, 'Recipe ID and comment are required', 400);
    exit();
}

$recipe_id = $data->recipe_id;
$user_id = $decoded['id']; // Extract user ID from the validated JWT token
$comment = $data->comment;

$sql = "INSERT INTO comments (recipe_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iis', $recipe_id, $user_id, $comment);

if ($stmt->execute()) {
    send_response(null, 'Comment added successfully', 200);
} else {
    send_response(null, 'Failed to add comment', 500);
}
?>
