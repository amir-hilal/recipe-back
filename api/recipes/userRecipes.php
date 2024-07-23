<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";
include '../utils/cors.php';
require_once "../utils/auth_middleware.php";

$decoded = authenticate_user_or_admin();

$user_id = $decoded['id'];

$sql = "SELECT id, title, description, rating FROM recipes WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);

send_response(['recipes' => $recipes], 'Recipes fetched successfully', 200);
?>
