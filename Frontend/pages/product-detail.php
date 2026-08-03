<?php
/**
 * E-Commerce Product Detail Page
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Product Details | Busia Chicken Farm';

include '../includes/header.php';

$pdo = getDB();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productSlug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

$product = null;
if ($pdo) {
    try {
        if ($productId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($productSlug !== '') {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1");
            $stmt->execute([$productSlug]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        @error_log("Failed to fetch product details: " . $e->getMessage());
    }
}

// Fallback to local data list if DB record not found
if (!$product) {
    require_once __DIR__ . '/../includes/product_source.php';
    $fallbackList = getFallbackProducts();
    foreach ($fallbackList as $p) {
        if (($productId > 0 && $p['id'] == $productId) || ($productSlug !== '' && $p['slug'] === $productSlug)) {
            $product = $p;
            break;
        }
    }
}

// If still not found, show error state
if (!$product) {
    ?>
    <div class="container" style="padding: 100px 20px; text-align: center;">
        <i data-lucide="alert-triangle" style="width: 64px; height: 64px; color: var(--accent); margin-bottom: 20px;"></i>
        <h2>Product Not Found</h2>
        <p style="color: var(--gray-600); margin-bottom: 30px;">The product you are looking for does not exist or has been removed.</p>
        <a href="/Frontend/pages/shop.php" class="btn btn-primary">Back to Shop</a>
    </div>
    <?php
    include '../includes/footer.php';
    exit;
}

$page_title = $product['name'] . ' - Buy Online | Busia Chicken Farm';
$inStock = ($product['stock_quantity'] ?? 0) > 0;
?>

<!-- Product Detail Hero / Breadcrumb -->
<section style="padding: var(--space-4xl) 0 var(--space-xl); background: #f8fafc; border-bottom: 1px solid rgba(0,0,0,0.05);">
    <div class="container">
        <div style="font-size: 0.88rem; color: var(--gray-500); margin-bottom: 15px; display: flex; gap: 8px; align-items: center;">
            <a href="/" style="color: inherit; text-decoration: none;">Home</a>
            <span>/</span>
            <a href="/Frontend/pages/shop.php" style="color: inherit; text-decoration: none;">Shop</a>
            <span>/</span>
            <span style="color: var(--primary); font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </div>
</section>

<!-- Detail Section -->
<section style="padding: var(--space-4xl) 0; background: #ffffff;">
    <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-4xl);">
        
        <!-- Left: Image Box -->
        <div style="background: #f8fafc; border-radius: var(--radius-lg); padding: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(0,0,0,0.05); min-height: 380px;">
            <img src="<?php echo htmlspecialchars($product['image_url'] ?? '/Frontend/images/download (4).png'); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 style="max-width: 100%; max-height: 320px; object-fit: contain; border-radius: var(--radius-md);">
        </div>

        <!-- Right: Content & Action Box -->
        <div>
            <span style="display: inline-block; padding: 4px 12px; background: rgba(27,94,32,0.08); color: var(--primary); font-weight: 700; border-radius: var(--radius-pill); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 15px;">
                <?php echo htmlspecialchars(str_replace('_', ' ', $product['product_type'] ?? 'Product')); ?>
            </span>
            
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--dark); margin-bottom: 10px; line-height: 1.2;">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 24px;">
                KES <?php echo number_format((float)$product['price'], 2); ?>
            </div>

            <p style="color: var(--gray-600); line-height: 1.75; font-size: 1rem; margin-bottom: var(--space-2xl);">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <!-- Stock Status -->
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 32px;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo $inStock ? '#16a34a' : '#dc2626'; ?>; display: inline-block;"></span>
                <span style="font-weight: 600; font-size: 0.9rem; color: <?php echo $inStock ? '#15803d' : '#b91c1c'; ?>;">
                    <?php echo $inStock ? 'In Stock (' . $product['stock_quantity'] . ' units available)' : 'Out of Stock'; ?>
                </span>
            </div>

            <!-- Quantity Selector & Cart Action -->
            <div style="display: flex; gap: 16px; align-items: center; max-width: 400px;">
                <?php if ($inStock): ?>
                    <div style="display: flex; align-items: center; border: 1px solid #cbd5e1; border-radius: var(--radius-md); background: #ffffff; overflow: hidden; height: 48px;">
                        <button onclick="adjustDetailQty(-1)" style="border: none; background: none; width: 40px; height: 100%; cursor: pointer; font-weight: bold; outline: none;">-</button>
                        <input type="number" id="detail-qty" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="border: none; width: 50px; text-align: center; font-weight: 600; height: 100%; outline: none;" readonly>
                        <button onclick="adjustDetailQty(1)" style="border: none; background: none; width: 40px; height: 100%; cursor: pointer; font-weight: bold; outline: none;">+</button>
                    </div>
                    
                    <button class="add-to-cart-btn btn btn-primary" id="detail-add-btn" data-id="<?php echo $product['id']; ?>" data-qty="1" style="flex: 1; height: 48px; justify-content: center; font-weight: 700; border-radius: var(--radius-md);">
                        <i data-lucide="shopping-cart"></i> Add to Cart
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline" style="flex: 1; height: 48px; justify-content: center; border-radius: var(--radius-md);" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<script>
function adjustDetailQty(delta) {
    const input = document.getElementById('detail-qty');
    if (!input) return;
    let val = parseInt(input.value) + delta;
    const maxVal = parseInt(input.max) || 100;
    if (val < 1) val = 1;
    if (val > maxVal) val = maxVal;
    input.value = val;

    // Sync button data-qty attribute
    const btn = document.getElementById('detail-add-btn');
    if (btn) {
        btn.setAttribute('data-qty', val);
    }
}
</script>

<?php
include '../includes/footer.php';
?>
