<?php
/**
 * Customer Forgot Password Page
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Recover Password | Busia Chicken Farm';

include '../includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = 'Please enter your email address.';
    } else {
        // Formulate a live mockup success flow since email recovery server is mock/local
        $message = 'A password recovery link has been sent to your email address: ' . htmlspecialchars($email);
    }
}
?>

<section style="padding: 120px 0 var(--space-4xl); background: #f8fafc; min-height: 70vh; display: flex; align-items: center;">
    <div class="container" style="display: flex; justify-content: center;">
        <div class="admin-card" style="width: 100%; max-width: 450px; padding: 40px; border-radius: var(--radius-lg); border: 1px solid rgba(0,0,0,0.06); background: #ffffff;">
            
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 8px;">Recover Password</h2>
                <p style="color: var(--gray-600); font-size: 0.95rem;">Enter your email and we'll send you a password reset link.</p>
            </div>

            <?php if ($message): ?>
                <div style="padding: 14px; background: #dcfce7; color: #15803d; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 20px; font-weight: 500; border-left: 4px solid #16a34a;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="padding: 14px; background: #fee2e2; color: #b91c1c; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 20px; font-weight: 500; border-left: 4px solid #dc2626;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--dark); margin-bottom: 6px;">Email Address</label>
                    <input type="email" name="email" class="form-control" style="width: 100%; height: 44px; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: var(--radius-md);" placeholder="e.g. user@example.com" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; justify-content: center; font-weight: 700; border-radius: var(--radius-md); margin-bottom: 20px;">
                    Send Reset Link
                </button>
            </form>

            <div style="text-align: center; font-size: 0.88rem; color: var(--gray-600);">
                Remember your password? <a href="/Frontend/pages/login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Login here</a>
            </div>

        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
