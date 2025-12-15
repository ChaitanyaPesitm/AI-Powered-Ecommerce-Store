<?php
/**
 * API Endpoint for Cart Actions
 * Handles add, update, remove, clear via AJAX
 */

require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

// Helper to send JSON response
function sendResponse($success, $message, $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get input data (support both POST and JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action = $input['action'] ?? '';

if (!$action) {
    sendResponse(false, 'No action specified');
}

try {
    switch ($action) {
        case 'add':
            $productId = (int)($input['product_id'] ?? 0);
            $qty = (int)($input['qty'] ?? 1);
            
            if ($productId <= 0) {
                sendResponse(false, 'Invalid product ID');
            }
            
            cart_add($productId, $qty);
            
            // Calculate total items
            $totalItems = 0;
            foreach ($_SESSION['cart'] as $q) $totalItems += $q;
            
            sendResponse(true, 'Product added to cart', ['cartCount' => $totalItems]);
            break;

        case 'add_by_name':
            $name = trim($input['name'] ?? '');
            if (empty($name)) {
                sendResponse(false, 'Product name is required');
            }

            // Search for the product
            $products = getProducts($name, 0, 1); // Limit 1
            
            if (empty($products)) {
                sendResponse(false, 'Product not found');
            }

            $product = $products[0];
            $productId = $product['id'];
            $qty = 1;

            cart_add($productId, $qty);

            // Calculate total items
            $totalItems = 0;
            foreach ($_SESSION['cart'] as $q) $totalItems += $q;

            sendResponse(true, 'Added ' . $product['name'] . ' to cart', [
                'cartCount' => $totalItems,
                'productName' => $product['name']
            ]);
            break;
            
        case 'remove_by_name':
            $name = trim($input['name'] ?? '');
            if (empty($name)) {
                sendResponse(false, 'Product name is required');
            }
            
            $removed = false;
            $removedName = '';
            
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $pid => $qty) {
                    $p = getProduct($pid);
                    if ($p && stripos($p['name'], $name) !== false) {
                        unset($_SESSION['cart'][$pid]);
                        $removed = true;
                        $removedName = $p['name'];
                        break; // Remove first match
                    }
                }
            }
            
            if ($removed) {
                $totalItems = 0;
                foreach ($_SESSION['cart'] as $q) $totalItems += $q;
                
                sendResponse(true, 'Removed ' . $removedName . ' from cart', [
                    'cartCount' => $totalItems
                ]);
            } else {
                sendResponse(false, 'Product not found in cart');
            }
            break;

        case 'update':
            $productId = (int)($input['product_id'] ?? 0);
            $qty = (int)($input['qty'] ?? 0);
            
            if ($productId <= 0) {
                sendResponse(false, 'Invalid product ID');
            }
            
            cart_update($productId, $qty);
            
            $totalItems = 0;
            foreach ($_SESSION['cart'] as $q) $totalItems += $q;
            
            sendResponse(true, 'Cart updated', [
                'cartCount' => $totalItems,
                'cartTotal' => cart_total(),
                'items' => cart_items()
            ]);
            break;
            
        case 'remove':
            $productId = (int)($input['product_id'] ?? 0);
            
            if ($productId <= 0) {
                sendResponse(false, 'Invalid product ID');
            }
            
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
            
            $totalItems = 0;
            foreach ($_SESSION['cart'] as $q) $totalItems += $q;
            
            sendResponse(true, 'Item removed', [
                'cartCount' => $totalItems,
                'cartTotal' => cart_total()
            ]);
            break;
            
        case 'clear':
            cart_clear();
            sendResponse(true, 'Cart cleared', ['cartCount' => 0]);
            break;
            
        case 'get_count':
            $totalItems = 0;
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $q) $totalItems += $q;
            }
            sendResponse(true, 'Count retrieved', ['cartCount' => $totalItems]);
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
} catch (Exception $e) {
    sendResponse(false, 'Error: ' . $e->getMessage());
}
