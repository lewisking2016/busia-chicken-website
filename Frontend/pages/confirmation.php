<?php
/**
 * Order Confirmation Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../';
$page_title = 'Order Confirmation - Busia Chicken Farm';

include '../includes/header.php';

// Redirect if no recent order
if (empty($_SESSION['last_order'])) {
    echo "<script>window.location.href = '/Frontend/pages/shop.php';</script>";
    exit;
}

$order = $_SESSION['last_order'];
// Clear the last order so refreshing doesn't show it again (optional, but good practice)
// unset($_SESSION['last_order']);
?>

<!-- Shop Hero -->
<section style="padding: var(--space-4xl) 0 var(--space-2xl); background-color: var(--gray-50); border-bottom: 1px solid var(--gray-200);">
    <div class="container" style="text-align: center;">
        <h1 style="margin-bottom: var(--space-sm);">Order Complete</h1>
        <p style="font-size: 1.125rem; color: var(--gray-600);">Thank you for shopping with Busia Chicken Farm.</p>
    </div>
</section>

<!-- Confirmation Content -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div style="width: 80px; height: 80px; background: #ECFDF5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); color: var(--success);">
                <i data-lucide="check-circle" style="width: 40px; height: 40px;"></i>
            </div>
            
            <h2 style="margin-bottom: var(--space-md);">Order Placed Successfully!</h2>
            <p style="color: var(--gray-600); margin-bottom: var(--space-2xl);">
                We've received your order and are processing it now. A confirmation email has been sent to you.
            </p>
            
            <div style="background-color: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-lg); padding: var(--space-2xl); text-align: left; margin-bottom: var(--space-2xl);">
                <h3 style="margin-bottom: var(--space-lg); border-bottom: 1px solid var(--gray-200); padding-bottom: var(--space-md);">Order Details</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                    <div>
                        <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Order Number</div>
                        <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($order['order_number']); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Date</div>
                        <div style="font-weight: 600; color: var(--dark);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                    <div>
                        <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Total Amount</div>
                        <div style="font-weight: 700; color: var(--primary);">KES <?php echo number_format((float)$order['total_amount'], 0); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Payment Method</div>
                        <div style="font-weight: 600; color: var(--dark); text-transform: capitalize;">
                            <?php 
                                if ($order['payment_method'] === 'mpesa') echo 'M-Pesa';
                                elseif ($order['payment_method'] === 'bank') echo 'Bank Transfer';
                                else echo 'Cash on Delivery';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($order['payment_method'] === 'mpesa'): ?>
                <div style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: var(--space-lg); margin-bottom: var(--space-2xl); text-align: left; display: flex; gap: var(--space-md); align-items: flex-start;">
                    <i data-lucide="info" style="color: #3B82F6; width: 24px; height: 24px; flex-shrink: 0;"></i>
                    <div>
                        <h4 style="color: #1D4ED8; margin-bottom: 8px;">M-Pesa Payment Instructions</h4>
                        <p style="color: #1E3A8A; font-size: 0.95rem; margin: 0;">
                            Please go to your M-Pesa menu, select Lipa na M-Pesa, Paybill, enter Business Number <strong>123456</strong>, Account Number <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>, and Amount <strong>KES <?php echo number_format((float)$order['total_amount'], 0); ?></strong>.
                        </p>
                    </div>
                </div>
            <?php elseif ($order['payment_method'] === 'bank'): ?>
                <div style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: var(--space-lg); margin-bottom: var(--space-2xl); text-align: left; display: flex; gap: var(--space-md); align-items: flex-start;">
                    <i data-lucide="info" style="color: #3B82F6; width: 24px; height: 24px; flex-shrink: 0;"></i>
                    <div>
                        <h4 style="color: #1D4ED8; margin-bottom: 8px;">Bank Transfer Instructions</h4>
                        <p style="color: #1E3A8A; font-size: 0.95rem; margin: 0;">
                            Please transfer the total amount to KCB Bank, Account Name: Busia Chicken Farm, Account No: <strong>1234567890</strong>. Use <strong><?php echo htmlspecialchars($order['order_number']); ?></strong> as the payment reference.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: var(--space-md); justify-content: center;">
                <a href="/Frontend/pages/shop.php" class="btn btn-outline">Continue Shopping</a>
                <a href="/Frontend/index.php" class="btn btn-primary">Go to Home</a>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
