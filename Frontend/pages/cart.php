<?php
/**
 * Shopping Cart Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../';
$page_title = 'Shopping Cart - Busia Chicken Farm';

include '../includes/header.php';

// Get database connection
$pdo = getDB();
$cart_items = [];
$subtotal = 0;
$delivery_charge = 0;
$total_amount = 0;

// Fetch cart items from session and database
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $productMap = [];
        foreach ($products as $p) {
            $productMap[$p['id']] = $p;
        }

        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            if (isset($productMap[$product_id])) {
                $product = $productMap[$product_id];
                $total = (float)$product['price'] * $quantity;
                $subtotal += $total;

                $cart_items[] = [
                    'id' => $product_id,
                    'name' => $product['name'],
                    'price' => (float)$product['price'],
                    'quantity' => $quantity,
                    'total' => $total,
                    'product_type' => $product['product_type'],
                    'image' => $product['image_url'] ?? ''
                ];
            }
        }
        
        $delivery_charge = ($subtotal >= 5000 || $subtotal == 0) ? 0 : 500;
        $total_amount = $subtotal + $delivery_charge;
    } catch (Exception $e) {
        error_log("Cart error: " . $e->getMessage());
    }
}
?>

<!-- Shop Hero -->
<section style="padding: var(--space-4xl) 0 var(--space-2xl); background-color: var(--gray-50); border-bottom: 1px solid var(--gray-200);">
    <div class="container" style="text-align: center;">
        <h1 style="margin-bottom: var(--space-sm);">Shopping Cart</h1>
        <p style="font-size: 1.125rem; color: var(--gray-600);">Review your items before proceeding to checkout.</p>
    </div>
</section>

<!-- Cart Content -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container">
        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div style="text-align: center; padding: var(--space-4xl) 0;">
                <div style="width: 80px; height: 80px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); color: var(--gray-400);">
                    <i data-lucide="shopping-cart" style="width: 40px; height: 40px;"></i>
                </div>
                <h2 style="margin-bottom: var(--space-md);">Your cart is empty</h2>
                <p style="color: var(--gray-600); margin-bottom: var(--space-2xl); max-width: 400px; margin-left: auto; margin-right: auto;">
                    Looks like you haven't added any products to your cart yet. Browse our shop to find the best poultry products.
                </p>
                <a href="/Frontend/pages/shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: var(--space-3xl); align-items: start;">
                
                <!-- Items List -->
                <div>
                    <div style="border: 1px solid var(--gray-200); border-radius: var(--radius-sm); overflow: hidden;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: var(--gray-50); border-bottom: 1px solid var(--gray-200);">
                                    <th style="padding: var(--space-lg); font-weight: 600; color: var(--dark);">Product</th>
                                    <th style="padding: var(--space-lg); font-weight: 600; color: var(--dark);">Price</th>
                                    <th style="padding: var(--space-lg); font-weight: 600; color: var(--dark);">Quantity</th>
                                    <th style="padding: var(--space-lg); font-weight: 600; color: var(--dark); text-align: right;">Total</th>
                                    <th style="padding: var(--space-lg);"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): 
                                    $img = $item['image'];
                                    if (!$img) {
                                        $img = match($item['product_type']) {
                                            'feed' => '/Frontend/images/Growers Mash.png',
                                            'eggs' => '/Frontend/images/download (3).png',
                                            'chicks' => '/Frontend/images/download (7).png',
                                            'live_chicken' => '/Frontend/images/download (4).png',
                                            default => '/Frontend/images/Chick Starter Crumbs.png'
                                        };
                                    }
                                ?>
                                <tr style="border-bottom: 1px solid var(--gray-200);">
                                    <td style="padding: var(--space-lg);">
                                        <div style="display: flex; align-items: center; gap: var(--space-md);">
                                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 64px; height: 64px; border-radius: 4px; object-fit: cover; border: 1px solid var(--gray-200);">
                                            <div>
                                                <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div style="font-size: 0.85rem; color: var(--gray-500); text-transform: capitalize;"><?php echo str_replace('_', ' ', $item['product_type']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: var(--space-lg); color: var(--gray-600);">KES <?php echo number_format($item['price'], 0); ?></td>
                                    <td style="padding: var(--space-lg);">
                                        <div style="display: flex; align-items: center; border: 1px solid var(--gray-200); border-radius: 4px; width: fit-content;">
                                            <button onclick="updateQty(<?php echo $item['id']; ?>, -1)" style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: var(--gray-600);"><i data-lucide="minus" style="width: 14px; height: 14px;"></i></button>
                                            <span id="qty-<?php echo $item['id']; ?>" style="padding: 0 0.5rem; min-width: 30px; text-align: center; font-weight: 600;"><?php echo $item['quantity']; ?></span>
                                            <button onclick="updateQty(<?php echo $item['id']; ?>, 1)" style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: var(--gray-600);"><i data-lucide="plus" style="width: 14px; height: 14px;"></i></button>
                                        </div>
                                    </td>
                                    <td style="padding: var(--space-lg); text-align: right; font-weight: 700; color: var(--dark);">KES <?php echo number_format($item['total'], 0); ?></td>
                                    <td style="padding: var(--space-lg); text-align: right;">
                                        <button onclick="removeItem(<?php echo $item['id']; ?>)" style="background: none; border: none; cursor: pointer; color: var(--gray-400); transition: color 0.2s;" onmouseover="this.style.color='var(--error)'" onmouseout="this.style.color='var(--gray-400)'">
                                            <i data-lucide="trash-2" style="width: 18px; height: 18px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: var(--space-xl); display: flex; justify-content: space-between; align-items: center;">
                        <a href="/Frontend/pages/shop.php" style="display: flex; align-items: center; gap: 8px; color: var(--gray-600); text-decoration: none; font-weight: 500;">
                            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                            Continue Shopping
                        </a>
                        <button onclick="clearCart()" style="background: none; border: none; color: var(--gray-500); cursor: pointer; font-size: 0.9rem; text-decoration: underline;">Clear Cart</button>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <aside>
                    <div style="background-color: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-sm); padding: var(--space-xl);">
                        <h3 style="margin-bottom: var(--space-xl); font-size: 1.25rem;">Order Summary</h3>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-md);">
                            <span style="color: var(--gray-600);">Subtotal</span>
                            <span style="font-weight: 600;">KES <?php echo number_format($subtotal, 0); ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-md);">
                            <span style="color: var(--gray-600);">Delivery</span>
                            <span style="font-weight: 600;">
                                <?php if ($delivery_charge == 0): ?>
                                    <span style="color: var(--success);">FREE</span>
                                <?php else: ?>
                                    KES <?php echo number_format($delivery_charge, 0); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: var(--space-lg) 0;">
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-xl);">
                            <span style="font-weight: 700; font-size: 1.125rem;">Total</span>
                            <span style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">KES <?php echo number_format($total_amount, 0); ?></span>
                        </div>
                        
                        <a href="/Frontend/pages/checkout.php" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                            Proceed to Checkout
                            <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                        </a>
                        
                        <div style="margin-top: var(--space-xl); display: flex; align-items: center; gap: 8px; justify-content: center; color: var(--gray-500); font-size: 0.85rem;">
                            <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i>
                            Secure Checkout
                        </div>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
async function updateQty(id, delta) {
    const qtyEl = document.getElementById(`qty-${id}`);
    let newQty = parseInt(qtyEl.textContent) + delta;
    if (newQty < 1) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('product_id', id);
        formData.append('quantity', newQty);
        
        const response = await fetch('/Backend/api/cart.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            location.reload(); // Refresh to update totals
        }
    } catch (e) {
        console.error(e);
    }
}

async function removeItem(id) {
    if (!confirm('Remove this item from cart?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', id);
        
        const response = await fetch('/Backend/api/cart.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (e) {
        console.error(e);
    }
}

async function clearCart() {
    if (!confirm('Clear all items from cart?')) return;
    
    try {
        const response = await fetch('/Backend/api/cart.php?action=clear');
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (e) {
        console.error(e);
    }
}
</script>

<?php
include '../includes/footer.php';
?>
