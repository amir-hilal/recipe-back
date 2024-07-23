<?php
require_once "../../config/config.php";
require_once "../utils/jwt.php";
require_once "../utils/response.php";
include '../utils/cors.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 5; // Number of recipes per page
$offset = ($page - 1) * $limit;

// Prepare the SQL query to join the recipes, users, and comments tables
$sql = "
    SELECT
        recipes.*,
        users.username AS author,
        GROUP_CONCAT(comments.comment SEPARATOR '||') AS comments
    FROM recipes
    JOIN users ON recipes.user_id = users.id
    LEFT JOIN comments ON recipes.id = comments.recipe_id
    GROUP BY recipes.id
    LIMIT ?, ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
$recipes = [];

while ($row = $result->fetch_assoc()) {
    $row['comments'] = $row['comments'] ? explode('||', $row['comments']) : [];
    $recipes[] = $row;
}

send_response($recipes, 'Recipes fetched successfully', 200);
?>
