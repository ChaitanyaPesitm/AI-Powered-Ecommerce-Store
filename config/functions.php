<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo->exec("INSERT IGNORE INTO categories (name) VALUES 
('Laptops'),('Smartphones'),('Accessories')");

$cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN, 0);
if (!in_array('description', $cols, true)) {
    $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL AFTER name");
}
if (!in_array('specifications', $cols, true)) {
    $pdo->exec("ALTER TABLE products ADD COLUMN specifications TEXT NULL AFTER description");
}

// ✅ Razorpay Schema Updates
$orderCols = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN, 0);
if (!in_array('razorpay_payment_id', $orderCols, true)) {
    $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_payment_id VARCHAR(255) NULL AFTER status");
}
if (!in_array('razorpay_order_id', $orderCols, true)) {
    $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_order_id VARCHAR(255) NULL AFTER razorpay_payment_id");
}
if (!in_array('razorpay_signature', $orderCols, true)) {
    $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_signature VARCHAR(255) NULL AFTER razorpay_order_id");
}
if (!in_array('payment_method', $orderCols, true)) {
    $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'Online' AFTER status");
}

// ✅ Returns/Exchanges Table
$pdo->exec("CREATE TABLE IF NOT EXISTS returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    type ENUM('return', 'exchange') NOT NULL DEFAULT 'return',
    reason VARCHAR(255) NOT NULL,
    description TEXT,
    refund_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'rejected', 'refunded', 'completed') DEFAULT 'pending',
    admin_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
)");

// ✅ Ensure 'type' column exists in returns table (for existing tables)
$returnCols = $pdo->query("SHOW COLUMNS FROM returns")->fetchAll(PDO::FETCH_COLUMN, 0);
if (!in_array('type', $returnCols, true)) {
    $pdo->exec("ALTER TABLE returns ADD COLUMN type ENUM('return', 'exchange') NOT NULL DEFAULT 'return' AFTER order_id");
}

// ✅ 3D Model Column
if (!in_array('model_glb', $cols, true)) {
    $pdo->exec("ALTER TABLE products ADD COLUMN model_glb VARCHAR(255) NULL AFTER image");
}

// ✅ Stories Table
$pdo->exec("CREATE TABLE IF NOT EXISTS stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    video VARCHAR(255) NULL,
    content_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

/** ---------------------------
 *  BASIC HELPERS
 * --------------------------- */
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Remove '/admin' or '/public' or '/api' or '/config' from the script directory to get the root
    $rootDir = preg_replace('#/(admin|public|api|config).*$#', '', $scriptDir);
    
    // Ensure rootDir ends with /
    $rootDir = rtrim($rootDir, '/') . '/';
    
    return $protocol . '://' . $host . $rootDir . ltrim($path, '/');
}

function redirect($path) {
    $url = base_url($path);
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    }
    echo '<script>window.location.href=' . json_encode($url) . ';</script>';
    echo '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/** ---------------------------
 *  CSRF SECURITY
 * --------------------------- */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/** ---------------------------
 *  LOGIN REQUIREMENTS
 * --------------------------- */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('public/login.php');
    }
}

function findUserByEmail($email) {
    global $pdo;
    $st = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    return $st->fetch();
}

/** ---------------------------
 *  CATEGORY + PRODUCT HELPERS
 * --------------------------- */
function getCategories() {
    global $pdo;
    return $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
}

function getProducts($q = '', $cat = 0, $limit = 20, $min_price = '', $max_price = '', $sort = 'newest') {
    global $pdo;

    $sql = "SELECT p.*, c.name AS category_name 
            FROM products p 
            JOIN categories c ON c.id = p.category_id 
            WHERE 1";
    $params = [];

    if ($q !== '') {
        $sql .= " AND (p.name LIKE :q OR p.description LIKE :q)";
        $params[':q'] = "%$q%";
    }

    if ((int)$cat > 0) {
        $sql .= " AND p.category_id = :cat";
        $params[':cat'] = (int)$cat;
    }
    
    if ($min_price !== '' && is_numeric($min_price)) {
        $sql .= " AND p.price >= :min_price";
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price !== '' && is_numeric($max_price)) {
        $sql .= " AND p.price <= :max_price";
        $params[':max_price'] = $max_price;
    }

    // Sorting
    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY p.id DESC";
            break;
    }

    $sql .= " LIMIT :limit";
    $st = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $st->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $st->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll();
}

function getProduct($id) {
    global $pdo;
    $st = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $st->execute([(int)$id]);
    return $st->fetch();
}

/** ---------------------------
 *  CART FUNCTIONS
 * --------------------------- */
function cart_init() {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
}

function cart_add($pid, $qty = 1) {
    cart_init();
    $pid = (int)$pid;
    $qty = max(1, (int)$qty);
    if (!isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid] = 0;
    $_SESSION['cart'][$pid] += $qty;
}

function cart_update($pid, $qty) {
    cart_init();
    $pid = (int)$pid;
    $qty = (int)$qty;
    if ($qty <= 0) unset($_SESSION['cart'][$pid]);
    else $_SESSION['cart'][$pid] = $qty;
}

function cart_items() {
    cart_init();
    $items = [];
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $p = getProduct($pid);
        if ($p) {
            $items[] = [
                'product' => $p,
                'qty' => $qty,
                'line_total' => $p['price'] * $qty
            ];
        }
    }
    return $items;
}

function cart_total() {
    $sum = 0;
    foreach (cart_items() as $it) $sum += $it['line_total'];
    return $sum;
}

function cart_clear() {
    $_SESSION['cart'] = [];
}

/** ---------------------------
 *  ORDERS + ORDER MANAGEMENT
 * --------------------------- */
function create_order($data, $items) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        $user_id = isLoggedIn() ? $_SESSION['user']['id'] : null;
        $payment_method = $data['payment_method'] ?? 'Online'; // Default to Online

        $st = $pdo->prepare("INSERT INTO orders 
            (user_id, customer_name, customer_email, customer_phone, customer_address, total, status, payment_method)
            VALUES (?,?,?,?,?,?, 'pending', ?)");
        $st->execute([
            $user_id,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            cart_total(),
            $payment_method
        ]);

        $order_id = $pdo->lastInsertId();

        $sti = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, qty) VALUES (?,?,?,?)");
        foreach ($items as $it) {
            $p = $it['product'];
            $qty = $it['qty'];
            $sti->execute([$order_id, $p['id'], $p['price'], $qty]);
            $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id=? AND stock >= ?")
                ->execute([$qty, $p['id'], $qty]);
        }

        $pdo->commit();
        return $order_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** ✅ Fetch orders with thumbnail for My Orders page */
function getUserOrdersWithThumbnail($user_id) {
    global $pdo;
    $st = $pdo->prepare("
        SELECT o.*,
          (SELECT p.image FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = o.id
            LIMIT 1) AS product_image
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.id DESC
    ");
    $st->execute([(int)$user_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
function cancelOrder($order_id, $user_id) {
    global $pdo;

    $st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
    $st->execute([$order_id, $user_id]);
    $order = $st->fetch();

    if (!$order) {
        throw new Exception('Order not found.');
    }

    if (!in_array(strtolower($order['status']), ['pending','processing'])) {
        throw new Exception('Order cannot be cancelled.');
    }

    // ✅ update order status
    $pdo->prepare("UPDATE orders SET status='Cancelled' WHERE id=?")->execute([$order_id]);

    // ✅ restock items
    $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
    $items->execute([$order_id]);
    foreach ($items as $item) {
        $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id=?")
            ->execute([$item['qty'], $item['product_id']]);
    }

    return true;
}

/** ---------------------------
 *  WISHLIST
 * --------------------------- */
function wishlist_add($user_id, $product_id) {
    global $pdo;
    $st = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    return $st->execute([(int)$user_id, (int)$product_id]);
}

function wishlist_remove($user_id, $product_id) {
    global $pdo;
    $st = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    return $st->execute([(int)$user_id, (int)$product_id]);
}

function wishlist_has($user_id, $product_id) {
    global $pdo;
    $st = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1");
    $st->execute([(int)$user_id, (int)$product_id]);
    return (bool)$st->fetchColumn();
}

function wishlist_items($user_id) {
    global $pdo;
    $st = $pdo->prepare("
        SELECT p.* 
        FROM wishlist w
        JOIN products p ON p.id = w.product_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $st->execute([(int)$user_id]);
    return $st->fetchAll();
}

function wishlist_count($user_id) {
    global $pdo;
    if (!$user_id) return 0;
    $st = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $st->execute([(int)$user_id]);
    return (int)$st->fetchColumn();
}

/** ---------------------------
 *  REVIEWS
 * --------------------------- */
function userPurchasedProduct($user_id, $product_id) {
    global $pdo;
    $st = $pdo->prepare("
        SELECT 1
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ?
        LIMIT 1
    ");
    $st->execute([(int)$user_id, (int)$product_id]);
    return (bool)$st->fetchColumn();
}

function hasUserReviewedProduct($user_id, $product_id) {
    global $pdo;
    $st = $pdo->prepare("SELECT 1 FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
    $st->execute([(int)$user_id, (int)$product_id]);
    return (bool)$st->fetchColumn();
}

function canUserReview($user_id, $product_id) {
    if (!$user_id) return false;
    return userPurchasedProduct($user_id, $product_id);
}

function addReview($user_id, $product_id, $order_id, $rating, $title, $comment) {
    global $pdo;
    $st = $pdo->prepare("
        INSERT INTO reviews (user_id, product_id, order_id, rating, title, comment)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ok = $st->execute([
        (int)$user_id,
        (int)$product_id,
        $order_id ? (int)$order_id : null,
        (int)$rating,
        $title,
        $comment
    ]);
    return $ok ? $pdo->lastInsertId() : false;
}

function getProductReviews($product_id) {
    global $pdo;
    $st = $pdo->prepare("
        SELECT r.*, u.name AS user_name
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ");
    $st->execute([(int)$product_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function getProductAverageRating($product_id) {
    global $pdo;
    $st = $pdo->prepare("
        SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS count_rating
        FROM reviews
        WHERE product_id = ?
    ");
    $st->execute([(int)$product_id]);
    return $st->fetch(PDO::FETCH_ASSOC);
}
