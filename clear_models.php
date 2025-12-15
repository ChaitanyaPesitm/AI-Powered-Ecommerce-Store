<?php
require_once __DIR__ . '/config/functions.php';

echo "🧹 Clearing Dummy 3D Models...\n";

// Set model_glb to NULL for all products
$pdo->exec("UPDATE products SET model_glb = NULL");

echo "✅ Removed 'Astronaut' model from all products.\n";
echo "ℹ️ The '3D View' button will now be HIDDEN on the product page until you upload a real .glb file.\n";
?>
