<?php
require_once 'config/functions.php';
$products = getProducts('', 0, 1000);
$output = "";
foreach ($products as $p) {
    $output .= "ID: " . $p['id'] . " | Name: " . $p['name'] . "\n";
}
file_put_contents('products_rename_list.txt', $output);
?>
