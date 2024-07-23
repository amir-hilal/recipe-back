<?php
require "../../config/config.php";
require "../utils/jwt.php";
require "../utils/response.php";
include '../utils/cors.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("email/username and password");

    $data = json_decode(file_get_contents('php://input'), true);

    $required_fields = ['login', 'password'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    $login = $data['login']; // This can be either email or username
    $password = $data['password'];

    // Prepare statements for both email and username
    $stmt = $conn->prepare('SELECT * FROM Users WHERE email = ? OR username = ?');
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $payload = [
            'id' => $user['id'],
            'role' => 'user',
            'email' => $user['email']
        ];
        $token = generate_jwt_token($payload);
        send_response(['token' => $token], 'Login successful', 200);
    } else {
        send_response(null, 'Invalid email or username or password', 401);
    }
} else {
    send_response(null, 'Wrong request method', 405);
}
?>
