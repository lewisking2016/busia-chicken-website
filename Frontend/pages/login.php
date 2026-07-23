<?php
/**
 * Login Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../';
$page_title = 'Login - Busia Chicken Farm';

include '../includes/header.php';
$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');

// Redirect only if a customer is already logged in
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'customer') {
    echo "<script>window.location.href = '/Frontend/pages/dashboard.php';</script>";
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please refresh and try again.';
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) $errors[] = 'Username or email is required';
    if (empty($password)) $errors[] = 'Password is required';

    if (empty($errors)) {
        $pdo = getDB();
        
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role, first_name, last_name FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                
                echo "<script>window.location.href = '/Frontend/index.php';</script>";
                exit;
            } else {
                $errors[] = 'Invalid username/email or password';
            }
        } catch (Exception $e) {
            $errors[] = 'An error occurred during login. Please try again.';
            if (APP_DEBUG) {
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}
?>

<!-- Login Section -->
<section style="padding: var(--space-4xl) 0; background-color: var(--gray-50); min-height: 80vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 450px;">
        <div style="background: var(--white); padding: var(--space-3xl); border-radius: var(--radius-lg); border: 1px solid var(--gray-200); box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: var(--space-2xl);">
                <div style="width: 48px; height: 48px; background: var(--primary); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md); color: white;">
                    <i data-lucide="log-in" style="width: 24px; height: 24px;"></i>
                </div>
                <h2>Welcome Back</h2>
                <p style="color: var(--gray-600); margin-top: var(--space-xs);">Enter your details to access your account.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="padding: 1rem; background-color: #FEF2F2; border-left: 4px solid var(--error); color: #991B1B; margin-bottom: var(--space-xl); border-radius: 4px;">
                    <ul style="margin: 0; padding-left: 1rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--gray-600); font-size: 0.95rem;">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#" style="color: var(--primary); font-size: 0.95rem; font-weight: 500;">Forgot Password?</a>
                </div>

                <button type="submit" name="login_submit" value="1" class="btn btn-primary" style="width: 100%; margin-bottom: var(--space-lg);">Sign In</button>
            </form>

            <div style="text-align: center; margin-top: var(--space-2xl); padding-top: var(--space-xl); border-top: 1px solid var(--gray-200);">
                <p style="color: var(--gray-600); font-size: 0.95rem;">
                    Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Create Account</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
