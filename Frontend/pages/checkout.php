<?php
/**
 * Checkout Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}

$path_prefix = '../';
$page_title = 'Checkout - Busia Chicken Farm';

include '../includes/header.php';
$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');
$mpesa_enabled = function_exists('getSetting') ? getSetting('mpesa_enabled', '1') === '1' : true;
$cod_enabled = function_exists('getSetting') ? getSetting('cod_enabled', '0') === '1' : false;

// Get database connection
$pdo = getDB();

// Redirect to cart if empty
if (empty($_SESSION['cart'])) {
    echo "<script>window.location.href = '/Frontend/pages/cart.php';</script>";
    exit;
}

// Calculate totals from database
$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$db_products = fetchAll($pdo, "SELECT * FROM products WHERE id IN ($placeholders)", $ids);

$productMap = [];
foreach ($db_products as $p) {
    $productMap[$p['id']] = $p;
}

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($productMap[$product_id])) {
        $subtotal += (float)$productMap[$product_id]['price'] * $quantity;
    }
}

$delivery_charge = ($subtotal >= 5000) ? 0 : 500;
$total_amount = $subtotal + $delivery_charge;
?>

<!-- Shop Hero -->
<section style="padding: var(--space-4xl) 0 var(--space-2xl); background-color: var(--gray-50); border-bottom: 1px solid var(--gray-200);">
    <div class="container" style="text-align: center;">
        <h1 style="margin-bottom: var(--space-sm);">Checkout</h1>
        <p style="font-size: 1.125rem; color: var(--gray-600);">Complete your order by providing delivery and payment details.</p>
    </div>
</section>

<!-- Checkout Content -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container">
        <form id="checkout-form" class="grid-2" style="gap: var(--space-4xl); align-items: start;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            
            <!-- Left: Delivery & Payment Details -->
            <div>
                <div style="margin-bottom: var(--space-3xl);">
                    <h3 style="margin-bottom: var(--space-xl); display: flex; align-items: center; gap: 12px;">
                        <span style="width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">1</span>
                        Delivery Information
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">First Name *</label>
                            <input type="text" name="first_name" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 4px; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Last Name *</label>
                            <input type="text" name="last_name" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 4px; outline: none;">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Email Address *</label>
                            <input type="email" name="email" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 4px; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="e.g. 0727585599" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 4px; outline: none;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: var(--space-lg);">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Delivery Address *</label>
                        <textarea name="address" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 4px; outline: none; min-height: 100px;"></textarea>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">City / Town *</label>
                        <input type="text" name="city" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 4px; outline: none;">
                    </div>
                </div>
                
                <div>
                    <h3 style="margin-bottom: var(--space-xl); display: flex; align-items: center; gap: 12px;">
                        <span style="width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">2</span>
                        Payment Method
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                        <?php if ($mpesa_enabled): ?>
                        <label style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-lg); border: 1px solid var(--gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="payment_method" value="mpesa" checked>
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; color: var(--dark);">M-Pesa</div>
                                <div style="font-size: 0.85rem; color: var(--gray-500);">Pay instantly using your phone</div>
                            </div>
                            <span style="display: inline-flex; align-items: center; justify-content: center; height: 24px; padding: 0 10px; border-radius: 9999px; background: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em;">
                                M-PESA
                            </span>
                        </label>
                        <?php endif; ?>
                        
                        <label style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-lg); border: 1px solid var(--gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="payment_method" value="bank" <?php echo $mpesa_enabled ? '' : 'checked'; ?>>
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; color: var(--dark);">Bank Transfer</div>
                                <div style="font-size: 0.85rem; color: var(--gray-500);">Direct bank deposit or transfer</div>
                            </div>
                            <i data-lucide="landmark" style="width: 24px; height: 24px; color: var(--gray-400);"></i>
                        </label>
                        <?php if ($cod_enabled): ?>
                        <label style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-lg); border: 1px solid var(--gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="payment_method" value="cod">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; color: var(--dark);">Cash on Delivery</div>
                                <div style="font-size: 0.85rem; color: var(--gray-500);">Pay when you receive your order</div>
                            </div>
                            <i data-lucide="truck" style="width: 24px; height: 24px; color: var(--gray-400);"></i>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right: Order Summary -->
            <aside>
                <div style="background-color: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-sm); padding: var(--space-xl); position: sticky; top: 100px;">
                    <h3 style="margin-bottom: var(--space-xl); font-size: 1.25rem;">Order Summary</h3>
                    
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: var(--space-xl);">
                        <?php foreach ($_SESSION['cart'] as $id => $qty): 
                            if (isset($productMap[$id])):
                                $p = $productMap[$id];
                        ?>
                        <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-md);">
                            <div style="position: relative;">
                            <?php
                                $fallbackImage = match($p['product_type'] ?? 'feed') {
                                    'feed' => '/Frontend/images/Growers Mash.png',
                                    'eggs' => '/Frontend/images/download (3).png',
                                    'chicks' => '/Frontend/images/download (7).png',
                                    'live_chicken' => '/Frontend/images/download (4).png',
                                    default => '/Frontend/images/Chick Starter Crumbs.png'
                                };
                            ?>
                            <img src="<?php echo $p['image_url'] ?: $fallbackImage; ?>" style="width: 56px; height: 56px; border-radius: 4px; object-fit: cover; border: 1px solid var(--gray-200);">
                                <span style="position: absolute; top: -8px; right: -8px; background: var(--dark); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;"><?php echo $qty; ?></span>
                            </div>
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; color: var(--dark); font-size: 0.95rem;"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div style="color: var(--gray-500); font-size: 0.85rem;">KES <?php echo number_format($p['price'], 0); ?></div>
                            </div>
                            <div style="font-weight: 600; color: var(--dark);">KES <?php echo number_format($p['price'] * $qty, 0); ?></div>
                        </div>
                        <?php endif; endforeach; ?>
                    </div>
                    
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
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                        Place Order
                        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    </button>
                    
                    <div style="margin-top: var(--space-xl); display: flex; align-items: center; gap: 8px; justify-content: center; color: var(--gray-500); font-size: 0.85rem;">
                        <i data-lucide="lock" style="width: 16px; height: 16px;"></i>
                        Secure Encryption
                    </div>
                </div>
            </aside>
        </form>
    </div>
</section>

<script>
document.getElementById('checkout-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" style="width: 18px; height: 18px;"></i> Processing...';
    lucide.createIcons();

    try {
        const formData = new FormData(e.target);
        const response = await fetch('/Backend/api/checkout.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Success - show confirmation
            BusiaApp.showNotification('Order placed successfully!', 'success');
            setTimeout(() => {
                window.location.href = '/Frontend/pages/confirmation.php';
            }, 1500);
        } else {
            BusiaApp.showNotification(result.message || 'Checkout failed', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Place Order <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>';
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Checkout error:', error);
        BusiaApp.showNotification('Error processing order', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Place Order <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>';
        lucide.createIcons();
    }
});
</script>

<?php
include '../includes/footer.php';
?>
