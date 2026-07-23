<?php
/**
 * Register Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../';
$page_title = 'Create Account - Busia Chicken Farm';

include '../includes/header.php';
$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');

// Redirect only if a customer is already logged in
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'customer') {
    echo "<script>window.location.href = '/Frontend/pages/dashboard.php';</script>";
    exit;
}

$errors = [];
$form_data = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'username' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please refresh and try again.';
    }

    $form_data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if (empty($form_data['first_name'])) $errors[] = 'First name is required';
    if (empty($form_data['last_name'])) $errors[] = 'Last name is required';
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) 
        $errors[] = 'Valid email is required';
    if (empty($form_data['phone'])) $errors[] = 'Phone number is required';
    if (empty($form_data['username']) || strlen($form_data['username']) < 3) 
        $errors[] = 'Username must be at least 3 characters';
    if (empty($password) || strlen($password) < 6) 
        $errors[] = 'Password must be at least 6 characters';
    if ($password !== $password_confirm) 
        $errors[] = 'Passwords do not match';

    if (empty($errors)) {
        $pdo = getDB();
        if ($pdo) {
            try {
                // Check if username or email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$form_data['username'], $form_data['email']]);
                if ($stmt->fetch()) {
                    $errors[] = 'Username or email already exists';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, phone, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
                    $stmt->execute([
                        $form_data['username'],
                        $hash,
                        $form_data['email'],
                        $form_data['phone'],
                        $form_data['first_name'],
                        $form_data['last_name']
                    ]);
                    
                    $_SESSION['registration_success'] = true;
                    echo "<script>window.location.href = '/Frontend/pages/login.php?success=1';</script>";
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = 'Registration failed. Please try again.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}
?>

<!-- Register Section -->
<section style="padding: var(--space-4xl) 0; background-color: var(--gray-50); min-height: 90vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 550px;">
        <div style="background: var(--white); padding: var(--space-3xl); border-radius: var(--radius-lg); border: 1px solid var(--gray-200); box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: var(--space-2xl);">
                <div style="width: 48px; height: 48px; background: var(--primary); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md); color: white;">
                    <i data-lucide="user-plus" style="width: 24px; height: 24px;"></i>
                </div>
                <h2>Create Account</h2>
                <p style="color: var(--gray-600); margin-top: var(--space-xs);">Join our poultry farming community today.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="padding: 1rem; background-color: #FEF2F2; border-left: 4px solid var(--error); color: #991B1B; margin-bottom: var(--space-xl); border-radius: 4px;">
                    <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="grid-2" style="margin-bottom: var(--space-lg);">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($form_data['first_name']); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($form_data['last_name']); ?>" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($form_data['email']); ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($form_data['phone']); ?>" placeholder="e.g. 0727..." class="form-control">
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($form_data['username']); ?>" class="form-control">
                </div>

                <div class="grid-2" style="margin-bottom: var(--space-xl);">
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirm</label>
                        <input type="password" id="password_confirm" name="password_confirm" required class="form-control">
                    </div>
                </div>

                <button type="submit" name="register_submit" value="1" class="btn btn-primary" style="width: 100%; margin-bottom: var(--space-lg);">Create Account</button>
            </form>

            <div style="text-align: center; padding-top: var(--space-lg); border-top: 1px solid var(--gray-200);">
                <p style="color: var(--gray-600); font-size: 0.95rem;">Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600;">Login here</a></p>
            </div>
        </div>

        <!-- Benefits -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-lg); margin-top: var(--space-3xl); text-align: center;">
            <div>
                <div style="color: var(--primary); margin-bottom: var(--space-sm);"><i data-lucide="shield-check" style="width: 24px; height: 24px; margin: 0 auto;"></i></div>
                <h4 style="font-size: 0.9rem; margin-bottom: 4px;">Secure</h4>
                <p style="font-size: 0.8rem; color: var(--gray-500);">Data protection</p>
            </div>
            <div>
                <div style="color: var(--primary); margin-bottom: var(--space-sm);"><i data-lucide="zap" style="width: 24px; height: 24px; margin: 0 auto;"></i></div>
                <h4 style="font-size: 0.9rem; margin-bottom: 4px;">Fast</h4>
                <p style="font-size: 0.8rem; color: var(--gray-500);">Quick checkout</p>
            </div>
            <div>
                <div style="color: var(--primary); margin-bottom: var(--space-sm);"><i data-lucide="award" style="width: 24px; height: 24px; margin: 0 auto;"></i></div>
                <h4 style="font-size: 0.9rem; margin-bottom: 4px;">Premium</h4>
                <p style="font-size: 0.8rem; color: var(--gray-500);">Exclusive offers</p>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
