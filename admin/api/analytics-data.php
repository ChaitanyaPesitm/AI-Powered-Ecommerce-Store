<?php
require_once __DIR__ . '/../../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    global $pdo;
    
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 365; // Default to 1 year
    $startDate = date('Y-m-d', strtotime("-$days days"));
    
    // 1. TOTAL SALES
    $salesQuery = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as order_count,
            COALESCE(SUM(total), 0) as daily_revenue
        FROM orders 
        WHERE created_at >= ?
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $salesQuery->execute([$startDate]);
    $dailySales = $salesQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    $totalRevenue = array_sum(array_column($dailySales, 'daily_revenue'));
    $totalOrders = array_sum(array_column($dailySales, 'order_count'));
    
    // 2. TOP PRODUCTS
    $topProducts = [];
    try {
        $topProductsQuery = $pdo->query("
            SELECT 
                p.id,
                p.name,
                p.price,
                SUM(oi.qty) as total_quantity
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id, p.name, p.price
            ORDER BY total_quantity DESC
            LIMIT 5
        ");
        $topProducts = $topProductsQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { }
    
    // 3. ORDERS BY STATUS
    $orderStatusQuery = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM orders
        WHERE created_at >= ?
        GROUP BY status
    ");
    $orderStatusQuery->execute([$startDate]);
    $ordersByStatus = $orderStatusQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // 4. USER STATS
    $userStats = ['new_customers' => 0, 'returning_customers' => 0];
    try {
        $userStatsQuery = $pdo->query("SELECT COUNT(DISTINCT user_id) as total_users FROM orders");
        $result = $userStatsQuery->fetch(PDO::FETCH_ASSOC);
        $userStats['new_customers'] = $result['total_users'] ?? 0;
    } catch (Exception $e) { }
    
    // 5. RECENT ORDERS
    $recentOrders = [];
    try {
        $recentOrdersQuery = $pdo->query("
            SELECT 
                o.id,
                o.total,
                o.status,
                o.created_at,
                COALESCE(u.name, o.customer_name, 'Guest') as username
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $recentOrders = $recentOrdersQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { }
    
    // 6. LOW STOCK
    $lowStock = [];
    try {
        $lowStockQuery = $pdo->query("
            SELECT id, name, stock, price
            FROM products
            WHERE stock < 10
            ORDER BY stock ASC
            LIMIT 10
        ");
        $lowStock = $lowStockQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { }

    // 7. CATEGORY REVENUE
    $categoryRevenue = [];
    try {
        $catRevQuery = $pdo->query("
            SELECT c.name as category, SUM(oi.price * oi.qty) as revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY revenue DESC
            LIMIT 5
        ");
        $categoryRevenue = $catRevQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { }

    // 8. CATEGORY RATINGS
    $categoryRatings = [];
    try {
        $catRateQuery = $pdo->query("
            SELECT c.name as category, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY avg_rating DESC
            LIMIT 5
        ");
        $categoryRatings = $catRateQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { }
    
    // BUILD RESPONSE
    $response = [
        'success' => true,
        'period' => [
            'days' => $days,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d')
        ],
        'summary' => [
            'total_revenue' => (float)$totalRevenue,
            'total_orders' => (int)$totalOrders,
            'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
            'revenue_growth' => 0
        ],
        'daily_sales' => array_reverse($dailySales),
        'top_products' => $topProducts,
        'category_revenue' => $categoryRevenue,
        'user_stats' => $userStats,
        'orders_by_status' => $ordersByStatus,
        'category_ratings' => $categoryRatings,
        'monthly_comparison' => [],
        'recent_orders' => $recentOrders,
        'low_stock_alerts' => $lowStock,
        'generated_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>
