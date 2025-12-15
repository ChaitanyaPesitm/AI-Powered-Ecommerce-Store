<?php
require_once __DIR__ . '/../config/functions.php';
global $pdo;
$stmt = $pdo->query("SELECT id, name FROM users LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    // Create a dummy user if none exists
    $pdo->query("INSERT INTO users (name, email, password_hash, role) VALUES ('Dummy User', 'dummy@example.com', 'hash', 'user')");
    $user_id = $pdo->lastInsertId();
} else {
    $user_id = $user['id'];
}
echo "User ID: " . $user_id;
