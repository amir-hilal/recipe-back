<?php
require "../../config/config.php";
require "../utils/response.php";
// require "../utils/send_verification_email.php";
require "../utils/validator.php";
include '../utils/cors.php';
date_default_timezone_set('Asia/Beirut');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (is_null($data)) {
        send_response(null, 'Invalid JSON input', 400);
        exit();
    }

    $required_fields = ['username', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || !validate_required($data[$field])) {
            send_response(null, "$field cannot be null or empty", 400);
            exit();
        }
    }

    if (!validate_string($data['username'])) {
        send_response(null, "Username must be an alphabetic string", 400);
        exit();
    }

    if (!validate_email($data['email'])) {
        send_response(null, "Invalid email address", 400);
        exit();
    }

    if (!validate_password($data['password'])) {
        send_response(null, "Password must contain at least 8 characters, one number, and one special character", 400);
        exit();
    }

    $username = $data['username'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $conn->prepare('SELECT * FROM Users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        send_response(null, 'Email is already registered', 400);
        exit();
    }

    $stmt = $conn->prepare('INSERT INTO Users (username, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param(
        'sss',
        $username,
        $email,
        $password
    );

    try {
        $stmt->execute();
        send_response(null, 'User Registered', 201);
    } catch (Exception $e) {
        send_response(null, $stmt->error, 500);
    }

} else {
    send_response(null, 'Wrong request method', 405);
}
?>
