<?php
/**
 * Database Query Helper Functions
 * Ready for implementation with actual PDO connection
 */
declare(strict_types=1);

/**
 * Get all products
 */
function getProducts(PDO $pdo, ?string $category = null, ?string $type = null): array
{
    $query = "SELECT * FROM products WHERE is_active = 1";
    $params = [];

    if ($category) {
        $query .= " AND category_id = (SELECT id FROM categories WHERE slug = :category)";
        $params['category'] = $category;
    }

    if ($type) {
        $query .= " AND product_type = :type";
        $params['type'] = $type;
    }

    $query .= " ORDER BY name ASC";

    return fetchAll($pdo, $query, $params);
}

/**
 * Get single product
 */
function getProduct(PDO $pdo, int $productId): ?array
{
    $query = "SELECT * FROM products WHERE id = :id AND is_active = 1";
    return fetchOne($pdo, $query, ['id' => $productId]);
}

/**
 * Create order
 */
function createOrder(PDO $pdo, array $orderData): string
{
    $query = "INSERT INTO orders (
        user_id, order_number, status, total_amount, payment_method, 
        shipping_address, phone_contact
    ) VALUES (
        :user_id, :order_number, :status, :total_amount, :payment_method,
        :shipping_address, :phone_contact
    )";

    $params = [
        'user_id' => $orderData['user_id'] ?? null,
        'order_number' => $orderData['order_number'],
        'status' => 'pending',
        'total_amount' => $orderData['total_amount'],
        'payment_method' => $orderData['payment_method'],
        'shipping_address' => $orderData['address'],
        'phone_contact' => $orderData['phone']
    ];

    execute($pdo, $query, $params);
    return $pdo->lastInsertId();
}

/**
 * Add order items
 */
function addOrderItems(PDO $pdo, int $orderId, array $items): void
{
    $query = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
              VALUES (:order_id, :product_id, :quantity, :price)";

    foreach ($items as $item) {
        $params = [
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
        execute($pdo, $query, $params);
    }
}

/**
 * Get orders for user
 */
function getUserOrders(PDO $pdo, int $userId): array
{
    $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    return fetchAll($pdo, $query, ['user_id' => $userId]);
}

/**
 * Get order details with items
 */
function getOrderDetails(PDO $pdo, int $orderId): ?array
{
    $orderQuery = "SELECT * FROM orders WHERE id = :id";
    $order = fetchOne($pdo, $orderQuery, ['id' => $orderId]);

    if (!$order) {
        return null;
    }

    $itemsQuery = "SELECT oi.*, p.name, p.sku FROM order_items oi
                   JOIN products p ON oi.product_id = p.id
                   WHERE oi.order_id = :order_id";
    $order['items'] = fetchAll($pdo, $itemsQuery, ['order_id' => $orderId]);

    return $order;
}

/**
 * Update order status
 */
function updateOrderStatus(PDO $pdo, int $orderId, string $status): bool
{
    $query = "UPDATE orders SET status = :status WHERE id = :id";
    return execute($pdo, $query, ['status' => $status, 'id' => $orderId]);
}

/**
 * Create user account
 */
function createUser(PDO $pdo, array $userData): string
{
    $query = "INSERT INTO users (
        username, email, password_hash, role, first_name, last_name, phone_number
    ) VALUES (
        :username, :email, :password_hash, :role, :first_name, :last_name, :phone_number
    )";

    $params = [
        'username' => $userData['username'],
        'email' => $userData['email'],
        'password_hash' => hashPassword($userData['password']),
        'role' => 'customer',
        'first_name' => $userData['first_name'],
        'last_name' => $userData['last_name'],
        'phone_number' => $userData['phone']
    ];

    execute($pdo, $query, $params);
    return $pdo->lastInsertId();
}

/**
 * Get user by username or email
 */
function getUserByCredentials(PDO $pdo, string $credential): ?array
{
    $query = "SELECT * FROM users WHERE username = :credential OR email = :credential";
    return fetchOne($pdo, $query, ['credential' => $credential]);
}

/**
 * Create flock record
 */
function createFlock(PDO $pdo, array $flockData): string
{
    $query = "INSERT INTO flocks (flock_name, breed, initial_count, current_count, hatch_date, status)
              VALUES (:name, :breed, :initial, :current, :hatch_date, 'active')";

    $params = [
        'name' => $flockData['name'],
        'breed' => $flockData['breed'],
        'initial' => $flockData['count'],
        'current' => $flockData['count'],
        'hatch_date' => $flockData['hatch_date']
    ];

    execute($pdo, $query, $params);
    return $pdo->lastInsertId();
}

/**
 * Record production data
 */
function recordProduction(PDO $pdo, array $productionData): string
{
    $query = "INSERT INTO production_records (
        flock_id, record_date, eggs_collected, cracked_eggs, meat_weight_kg, 
        mortality, feed_consumed_kg, notes
    ) VALUES (
        :flock_id, :record_date, :eggs, :cracked, :meat, :mortality, :feed, :notes
    )";

    $params = [
        'flock_id' => $productionData['flock_id'],
        'record_date' => $productionData['date'],
        'eggs' => $productionData['eggs'] ?? 0,
        'cracked' => $productionData['cracked'] ?? 0,
        'meat' => $productionData['meat'] ?? 0,
        'mortality' => $productionData['mortality'] ?? 0,
        'feed' => $productionData['feed'] ?? 0,
        'notes' => $productionData['notes'] ?? ''
    ];

    execute($pdo, $query, $params);
    return $pdo->lastInsertId();
}

/**
 * Get farm statistics
 */
function getFarmStats(PDO $pdo, int $userId): array
{
    // Total flocks
    $flockQuery = "SELECT COUNT(*) as count FROM flocks";
    $flocks = fetchOne($pdo, $flockQuery);

    // Total production
    $prodQuery = "SELECT SUM(eggs_collected) as eggs, SUM(meat_weight_kg) as meat 
                  FROM production_records WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $production = fetchOne($pdo, $prodQuery);

    // Recent orders
    $orderQuery = "SELECT COUNT(*) as count, SUM(total_amount) as revenue 
                   FROM orders WHERE user_id = :user_id";
    $orders = fetchOne($pdo, $orderQuery, ['user_id' => $userId]);

    return [
        'flocks' => $flocks['count'] ?? 0,
        'eggs_month' => $production['eggs'] ?? 0,
        'meat_month' => $production['meat'] ?? 0,
        'total_orders' => $orders['count'] ?? 0,
        'total_spent' => $orders['revenue'] ?? 0
    ];
}

/**
 * Get all categories
 */
function getCategories(PDO $pdo): array
{
    $query = "SELECT * FROM categories ORDER BY name ASC";
    return fetchAll($pdo, $query);
}

/**
 * Add product
 */
function addProduct(PDO $pdo, array $productData): string
{
    $query = "INSERT INTO products (
        category_id, name, slug, description, product_type, price, 
        stock_quantity, weight_kg, sku, manufacturer, is_active
    ) VALUES (
        :category_id, :name, :slug, :description, :product_type, :price,
        :stock, :weight, :sku, :manufacturer, 1
    )";

    $params = [
        'category_id' => $productData['category_id'],
        'name' => $productData['name'],
        'slug' => str_replace(' ', '-', strtolower($productData['name'])),
        'description' => $productData['description'] ?? '',
        'product_type' => $productData['product_type'],
        'price' => $productData['price'],
        'stock' => $productData['stock_quantity'] ?? 0,
        'weight' => $productData['weight_kg'] ?? 0,
        'sku' => $productData['sku'] ?? '',
        'manufacturer' => $productData['manufacturer'] ?? ''
    ];

    execute($pdo, $query, $params);
    return $pdo->lastInsertId();
}

/**
 * Update product stock
 */
function updateProductStock(PDO $pdo, int $productId, int $newStock): bool
{
    $query = "UPDATE products SET stock_quantity = :stock WHERE id = :id";
    return execute($pdo, $query, ['stock' => $newStock, 'id' => $productId]);
}

/**
 * Record financial transaction
 */
function recordTransaction(PDO $pdo, array $transactionData): string
{
    $query = "INSERT INTO financial_records (type, category, amount, transaction_date, description)
              VALUES (:type, :category, :amount, :date, :description)";

    $params = [
        'type' => $transactionData['type'], // 'income' or 'expense'
        'category' => $transactionData['category'],
        'amount' => $transactionData['amount'],
        'date' => $transactionData['date'],
        'description' => $transactionData['description'] ?? ''
    ];

    execute($pdo, $query, $params);
    return $pdo->lastInsertId();
}

?>
