<?php
require_once __DIR__ . '/../config/functions.php';
global $pdo;
$stmt = $pdo->query("DESCRIBE reviews");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
