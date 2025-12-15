<?php
require_once __DIR__ . '/../config/functions.php';
global $pdo;
$stmt = $pdo->query("SELECT id, username FROM users LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
