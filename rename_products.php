<?php
require_once 'config/functions.php';

$updates = [
    11 => 'Galaxy Watch',
    10 => 'Galaxy S25',
    9 => 'Vivobook',
    8 => 'HP Keyboard',
    7 => 'iPhone 16',
    6 => 'Bose Headphones',
    5 => 'Corsair Mouse',
    4 => 'HP Omen',
    3 => 'Dell Mouse',
    2 => 'Sony Camera',
    1 => 'Marshall Speaker'
];

foreach ($updates as $id => $newName) {
    $stmt = $pdo->prepare("UPDATE products SET name = ? WHERE id = ?");
    $stmt->execute([$newName, $id]);
    echo "Updated ID $id to '$newName'\n";
}
?>
