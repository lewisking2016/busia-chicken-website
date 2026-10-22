<?php
/**
 * Admin Login Page
 * Split layout: farm identity + farm imagery on the left, sign-in on the right.
 * Collapses to a single centred card with a compact brand header on small
 * screens so the page never shows an empty void.
 */
declare(strict_types=1);

// Use temp session path
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$path_prefix = '../';
$page_title = 'Admin Portal - Busia Chicken Farm';

// Load frontend config
require_once __DIR__ . '/../includes/config.php';

$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');

function isSafeAdminRedirect(string $path): bool
{
    if ($path === '') {
        return false;
    }

    if (!str_starts_with($path, '/')) {
        return false;
    }

    if (str_starts_with($path, '//') || str_contains($path, '://') || str_contains($path, '\\')) {
        return false;
    }

    return str_starts_with($path, '/Frontend/admin/') || $path === '/busiaadmin';
}

// Redirect if already logged in as admin
if (!empty($_SESSION['user_id']) && in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager','stock_manager','sales_staff'], true)) {
    header('Location: /Frontend/admin/dashboard.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please refresh and try again.';
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Please enter both username and password';
    } else {
        $pdo = getDB();
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role, first_name FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                if (!in_array($user['role'], ['super_admin','farm_manager','stock_manager','sales_staff'], true)) {
                    $errors[] = 'Access denied. You do not have permission to use the admin panel.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];
                    logActivity($pdo, 'login', 'auth', "{$user['username']} logged in", (int)$user['id'], 'user');

                    $next = $_GET['next'] ?? '/Frontend/admin/products.php';
                    if (!isSafeAdminRedirect($next)) {
                        $next = '/Frontend/admin/products.php';
                    }
                    header('Location: ' . $next);
                    exit;
                }
            } else {
                $errors[] = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $errors[] = 'System error occurred during login';
            if (defined('APP_DEBUG') && APP_DEBUG) error_log('Admin login error: ' . $e->getMessage());
        }
    }
}

// What staff actually do in here — naming real modules is more useful than
// generic marketing claims, and it stays true as the farm's work changes.
$modules = [
    ['egg',      'Eggs &amp; production'],
    ['wheat',    'Feed &amp; materials'],
    ['hand-coins','Sales, credit &amp; cash'],
    ['package',  'Store &amp; inventory'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Scoped to this page. Nothing here loads the shared site stylesheets,
           so the login screen can never be restyled by them. */
        :root {
            --b-green-900: #0B3D17;
            --b-green-800: #124A1F;
            --b-green-700: #14532D;
            --b-green-600: #1B5E20;
            --b-green-500: #2E7D32;
            --b-gold-500:  #E0A82E;
            --b-gold-400:  #F0B429;
            --b-ink:       #0F1B12;
            --b-text:      #1E293B;
            --b-muted:     #64748B;
            --b-line:      #E2E8F0;
            --b-surface:   #F8FAFC;
            --b-radius:    12px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--b-text);
            background: var(--b-surface);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Shell ─────────────────────────────────────────── */
        .b-login {
            display: flex;
            min-height: 100vh;
            min-height: 100dvh;
        }

        /* ── Brand panel ───────────────────────────────────── */
        .b-brand {
            position: relative;
            flex: 1.05;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(2rem, 4vw, 3.5rem);
            overflow: hidden;
            isolation: isolate;
            background: var(--b-green-900);
        }

        .b-brand__img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
            /* Keep the photo present but subordinate to the type. */
            opacity: 0.38;
            filter: saturate(1.05);
        }

        /* Deep green scrim: guarantees text contrast over any photo. */
        .b-brand__scrim {
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(160deg, rgba(11,61,23,0.94) 0%, rgba(11,61,23,0.72) 55%, rgba(8,47,19,0.94) 100%);
        }

        .b-brand__logo {
            height: 58px;
            width: auto;
            background: rgba(255,255,255,0.94);
            border-radius: 10px;
            padding: 7px 12px;
            box-sizing: content-box;
        }

        .b-brand__body { max-width: 30rem; margin: auto 0; }

        .b-brand__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--b-gold-400);
            margin: 0 0 1rem;
        }
        .b-brand__eyebrow::before {
            content: '';
            width: 18px;
            height: 2px;
            background: var(--b-gold-400);
            border-radius: 2px;
        }

        .b-brand h1 {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2rem, 3.6vw, 3rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -0.02em;
            color: #fff;
            margin: 0 0 0.9rem;
        }

        .b-brand__lede {
            font-size: 1.02rem;
            line-height: 1.65;
            color: rgba(255,255,255,0.82);
            margin: 0 0 2rem;
        }

        /* Real modules, not marketing claims. */
        .b-modules {
            display: grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap: 0.6rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .b-modules li {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.6rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 9px;
        }
        .b-modules svg { width: 16px; height: 16px; color: var(--b-gold-400); flex-shrink: 0; }

        .b-brand__foot {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.6);
        }

        /* ── Form panel ────────────────────────────────────── */
        .b-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.75rem, 4vw, 3rem);
            background: #fff;
        }

        .b-card { width: 100%; max-width: 25rem; }

        /* Compact brand header — hidden on desktop, shown when the brand
           panel collapses, so the page is never a bare form in a void. */
        .b-card__brand {
            display: none;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--b-line);
        }
        .b-card__brand img { height: 42px; width: auto; }
        .b-card__brand strong {
            font-family: 'Outfit', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--b-green-700);
            letter-spacing: -0.01em;
        }

        .b-card h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--b-ink);
            margin: 0 0 0.35rem;
        }
        .b-card__sub {
            color: var(--b-muted);
            font-size: 0.94rem;
            margin: 0 0 1.9rem;
        }

        /* Errors */
        .b-alert {
            display: flex;
            gap: 0.6rem;
            align-items: flex-start;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-left: 3px solid #DC2626;
            border-radius: 9px;
            padding: 0.8rem 0.9rem;
            margin-bottom: 1.4rem;
            color: #991B1B;
            font-size: 0.87rem;
            line-height: 1.5;
        }
        .b-alert svg { width: 17px; height: 17px; flex-shrink: 0; margin-top: 1px; }

        /* Fields */
        .b-field { margin-bottom: 1.15rem; }
        .b-field label {
            display: block;
            font-size: 0.83rem;
            font-weight: 600;
            color: var(--b-text);
            margin-bottom: 0.42rem;
        }

        .b-input { position: relative; display: flex; align-items: center; }
        .b-input > svg {
            position: absolute;
            left: 0.85rem;
            width: 18px;
            height: 18px;
            color: var(--b-muted);
            pointer-events: none;
            transition: color 0.18s ease;
        }
        .b-input input {
            width: 100%;
            padding: 0.78rem 0.9rem 0.78rem 2.7rem;
            font: inherit;
            font-size: 0.95rem;
            color: var(--b-ink);
            background: var(--b-surface);
            border: 1px solid var(--b-line);
            border-radius: 10px;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }
        /* Room for the reveal button on the password row. */
        .b-input--pw input { padding-right: 2.9rem; }
        .b-input input::placeholder { color: #94A3B8; }

        .b-input input:hover { border-color: #CBD5E1; }

        .b-input input:focus {
            background: #fff;
            border-color: var(--b-green-600);
            box-shadow: 0 0 0 3px rgba(27,94,32,0.12);
        }
        .b-input input:focus + svg,
        .b-input:focus-within > svg { color: var(--b-green-600); }

        .b-reveal {
            position: absolute;
            right: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 8px;
            color: var(--b-muted);
            cursor: pointer;
            transition: color 0.18s ease, background 0.18s ease;
        }
        .b-reveal:hover { color: var(--b-green-700); background: #EEF7F0; }
        .b-reveal svg { width: 17px; height: 17px; }

        .b-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.6rem;
            font-size: 0.87rem;
        }
        .b-check {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--b-muted);
            cursor: pointer;
            user-select: none;
        }
        .b-check input {
            width: 16px;
            height: 16px;
            accent-color: var(--b-green-600);
            cursor: pointer;
        }
        .b-link {
            color: var(--b-green-700);
            font-weight: 600;
            text-decoration: none;
        }
        .b-link:hover { text-decoration: underline; }

        .b-submit {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.88rem 1.25rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background: var(--b-green-600);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 6px 16px -6px rgba(27,94,32,0.5);
            transition: background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }
        .b-submit svg { width: 18px; height: 18px; }
        .b-submit:hover {
            background: var(--b-green-500);
            transform: translateY(-1px);
            box-shadow: 0 10px 22px -8px rgba(27,94,32,0.6);
        }
        .b-submit:active { transform: translateY(0); }

        .b-card__foot {
            margin-top: 2rem;
            padding-top: 1.4rem;
            border-top: 1px solid var(--b-line);
            text-align: center;
            font-size: 0.8rem;
            color: var(--b-muted);
        }
        .b-card__foot a { color: var(--b-green-700); font-weight: 600; text-decoration: none; }
        .b-card__foot a:hover { text-decoration: underline; }

        /* Visible keyboard focus everywhere. */
        a:focus-visible, button:focus-visible, input:focus-visible {
            outline: 2px solid var(--b-green-600);
            outline-offset: 2px;
            border-radius: 6px;
        }
        .b-input input:focus-visible { outline: none; }

        /* ── Responsive ────────────────────────────────────── */
        /* Below this width the brand panel folds away, so the compact header
           takes over and the card stays visually anchored. */
        @media (max-width: 900px) {
            .b-brand { display: none; }
            .b-card__brand { display: flex; }
            .b-panel { background: var(--b-surface); }
            .b-card {
                background: #fff;
                border: 1px solid var(--b-line);
                border-radius: 16px;
                padding: clamp(1.5rem, 5vw, 2.25rem);
                box-shadow: 0 20px 45px -28px rgba(15,23,42,0.35);
                max-width: 27rem;
            }
        }

        @media (max-width: 420px) {
            .b-modules { grid-template-columns: 1fr; }
            .b-row { gap: 0.6rem; }
        }

        /* ── Motion ────────────────────────────────────────── */
        .b-anim { animation: b-rise 0.5s cubic-bezier(0.16,1,0.3,1) both; }
        .b-brand__body { animation-delay: 0.08s; }
        .b-card { animation-delay: 0.04s; }

        @keyframes b-rise {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-delay: 0ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="b-login">

        <!-- Brand panel -->
        <section class="b-brand">
            <img src="/Frontend/images/adbg.png" alt="" class="b-brand__img" aria-hidden="true">
            <div class="b-brand__scrim"></div>

            <img src="/Frontend/images/busia logo.png" alt="Busia Chicken Farm" class="b-brand__logo b-anim">

            <div class="b-brand__body b-anim">
                <p class="b-brand__eyebrow">Farm management system</p>
                <h1>Every flock, every crate, every shilling.</h1>
                <p class="b-brand__lede">
                    One place to record what the farm produces, what it costs,
                    and what you are owed.
                </p>
                <ul class="b-modules">
                    <?php foreach ($modules as [$icon, $label]): ?>
                    <li>
                        <i data-lucide="<?= $icon ?>"></i>
                        <span><?= $label ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <p class="b-brand__foot b-anim">
                Busia Chicken Farm &middot; Nasira AC, Busia County, Kenya
            </p>
        </section>

        <!-- Sign-in -->
        <section class="b-panel">
            <div class="b-card b-anim">

                <div class="b-card__brand">
                    <img src="/Frontend/images/busia logo.png" alt="Busia Chicken Farm">
                    <strong>Busia Chicken Farm</strong>
                </div>

                <h2>Sign in</h2>
                <p class="b-card__sub">Enter your details to open the farm dashboard.</p>

                <?php if (!empty($errors)): ?>
                    <div class="b-alert" role="alert">
                        <i data-lucide="alert-circle"></i>
                        <div>
                            <?php foreach ($errors as $err): ?>
                                <div><?php echo htmlspecialchars($err); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="b-field">
                        <label for="b-user">Username or email</label>
                        <div class="b-input">
                            <input type="text" id="b-user" name="username"
                                   value="<?php echo htmlspecialchars($username); ?>"
                                   required autofocus autocomplete="username"
                                   placeholder="you@busiachicken.com">
                            <i data-lucide="user"></i>
                        </div>
                    </div>

                    <div class="b-field">
                        <label for="b-pass">Password</label>
                        <div class="b-input b-input--pw">
                            <input type="password" id="b-pass" name="password"
                                   required autocomplete="current-password"
                                   placeholder="Your password">
                            <i data-lucide="lock"></i>
                            <button type="button" class="b-reveal" id="b-reveal"
                                    aria-label="Show password" aria-pressed="false" title="Show password">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="b-row">
                        <label class="b-check">
                            <input type="checkbox" name="remember" value="1">
                            <span>Keep me signed in</span>
                        </label>
                        <a href="/Frontend/pages/forgot-password.php" class="b-link">Forgot password?</a>
                    </div>

                    <button type="submit" name="admin_login" class="b-submit">
                        <span>Sign in to dashboard</span>
                        <i data-lucide="arrow-right"></i>
                    </button>
                </form>

                <div class="b-card__foot">
                    &copy; <?php echo date('Y'); ?> Busia Chicken Farm
                    &middot; <a href="/">Back to website</a>
                </div>
            </div>
        </section>

    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Show / hide password
        var pass = document.getElementById('b-pass');
        var btn  = document.getElementById('b-reveal');
        if (pass && btn) {
            btn.addEventListener('click', function () {
                var showing = pass.type === 'text';
                pass.type = showing ? 'password' : 'text';
                btn.setAttribute('aria-pressed', showing ? 'false' : 'true');
                btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                btn.setAttribute('title', showing ? 'Show password' : 'Hide password');
                btn.innerHTML = '<i data-lucide="' + (showing ? 'eye' : 'eye-off') + '"></i>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                pass.focus();
            });
        }
    });
    </script>
</body>
</html>