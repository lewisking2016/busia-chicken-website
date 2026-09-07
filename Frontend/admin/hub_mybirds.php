<?php
/**
 * Hub: My Birds (simplified) — 4 combined pages so farmers never face 9
 * separate modules. Each group hosts its related tools as tabs inside ONE
 * screen; every module keeps working exactly as before (embedded frame with
 * its duplicate admin chrome hidden).
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager','stock_manager','sales_staff'], true)) {
    echo "<script>window.location.href='/busiaadmin';</script>"; exit;
}

$page_title = 'My Birds - Admin';
include __DIR__ . '/includes/admin_header.php';

$groups = [
    'flocks' => [
        'icon'  => 'bird',
        'label' => 'Flocks & Houses',
        'desc'  => 'All your birds: add flocks, track bird counts, and manage houses.',
        'tabs'  => [
            'flocks'  => ['icon' => 'layers',    'label' => 'My Flocks',     'url' => 'flocks.php'],
            'houses'  => ['icon' => 'home',       'label' => 'Houses & Batches', 'url' => 'batches.php'],
        ],
    ],
    'eggs' => [
        'icon'  => 'egg',
        'label' => 'Eggs & Losses',
        'desc'  => 'Eggs collected, broken eggs (collection, transport, storage) and quality checks.',
        'tabs'  => [
            'production' => ['icon' => 'clipboard-list', 'label' => 'Eggs Collected', 'url' => 'production.php'],
            'losses'     => ['icon' => 'heart-crack',    'label' => 'Broken / Lost Eggs', 'url' => 'extras.php'],
            'quality'    => ['icon' => 'flask-conical',  'label' => 'Quality Tests', 'url' => 'extras.php?tab=quality'],
        ],
    ],
    'health' => [
        'icon'  => 'heart-pulse',
        'label' => 'Health & Vaccines',
        'desc'  => 'Sick birds, vet visits and the vaccine schedule for every flock.',
        'tabs'  => [
            'health'      => ['icon' => 'heart-pulse', 'label' => 'Sick Birds & Vet', 'url' => 'health.php'],
            'vaccines'    => ['icon' => 'syringe',     'label' => 'Vaccines',        'url' => 'vaccinations.php'],
        ],
    ],
    'growth' => [
        'icon'  => 'trending-up',
        'label' => 'Growth & Feeding',
        'desc'  => 'Broilers, hatchery chicks and feeding programs.',
        'tabs'  => [
            'broilers' => ['icon' => 'drumstick', 'label' => 'Broilers',   'url' => 'broiler.php'],
            'hatchery' => ['icon' => 'baby',      'label' => 'Hatchery',   'url' => 'hatchery.php'],
            'feeding'  => ['icon' => 'wheat',     'label' => 'Feeding',    'url' => 'feeding.php'],
        ],
    ],
];

$group = $_GET['group'] ?? 'flocks';
if (!isset($groups[$group])) $group = 'flocks';
$activeGroup = $groups[$group];

$baseDir = __DIR__ . '/';
?>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .mb-hub-iframe { width: 100%; border: 1px solid var(--admin-border); border-radius: 8px; background: #fff; }
    #mb-frame-shell { position: relative; }
    #mb-spinner {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: #f8fafc; z-index: 5; transition: opacity 0.2s ease;
    }
    #mb-spinner.hidden { opacity: 0; pointer-events: none; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;"><?= htmlspecialchars($activeGroup['label'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;"><?= htmlspecialchars($activeGroup['desc'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</div>

<!-- Group switcher (the 4 combined pages) -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:16px;overflow-x:auto;scrollbar-width:none;">
<?php foreach ($groups as $gk => $g): ?>
    <a href="?group=<?= $gk ?>" style="display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:700;font-size:0.86rem;transition:all 0.18s;<?= $group === $gk ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.10);' : 'color:#64748b;' ?>">
        <i data-lucide="<?= $g['icon'] ?>" style="width:15px;height:15px;"></i> <?= $g['label'] ?>
    </a>
<?php endforeach; ?>
</div>

<!-- Tool tabs inside this group (switch module without leaving the page) -->
<div style="display:flex;gap:4px;background:#fff;border:1px solid var(--admin-border);padding:5px;border-radius:10px;margin-bottom:16px;overflow-x:auto;scrollbar-width:none;">
<?php foreach ($activeGroup['tabs'] as $tk => $t): ?>
    <button type="button" data-mb-url="<?= $t['url'] ?>" data-mb-key="<?= $tk ?>"
            class="mb-tab-btn"
            style="display:flex;align-items:center;gap:7px;padding:9px 15px;border:none;border-radius:8px;background:transparent;color:#64748b;cursor:pointer;white-space:nowrap;font-weight:600;font-size:0.84rem;font-family:'Outfit',sans-serif;transition:all 0.18s;">
        <i data-lucide="<?= $t['icon'] ?>" style="width:15px;height:15px;"></i> <?= $t['label'] ?>
    </button>
<?php endforeach; ?>
</div>

<!-- Embedded module -->
<div id="mb-frame-shell">
    <div id="mb-spinner">
        <div style="display:inline-flex;align-items:center;gap:10px;color:#64748b;font-weight:600;font-size:0.9rem;">
            <div style="width:22px;height:22px;border:2px solid #cbd5e1;border-top-color:var(--admin-primary);border-radius:50%;animation:spin 0.8s linear infinite;"></div>
            Loading…
        </div>
    </div>
    <iframe id="mb-frame" class="mb-hub-iframe" title="<?= htmlspecialchars($activeGroup['label'], ENT_QUOTES, 'UTF-8') ?>" style="height:calc(100vh - 265px);min-height:540px;"></iframe>
</div>

<style>
    @media (max-width: 900px) { #mb-frame { height: 72vh !important; min-height: 480px; } }
    @media (max-width: 640px) { #mb-frame { height: 78vh !important; min-height: 420px; } }
</style>

<script>
(function () {
    const frame = document.getElementById('mb-frame');
    const spinner = document.getElementById('mb-spinner');
    const btns = Array.from(document.querySelectorAll('.mb-tab-btn'));
    const group = <?= json_encode($group) ?>;
    const storeKey = 'busiaHubBirds:' + group;
    const HIDE_DUPLICATE_CHROME =
        '#admin-nav,#admin-nav-backdrop,.admin-top-bar,#admin-nav-toggle,#admin-nav-help,' +
        '#detail-view-toggle,#open-system-guide{display:none !important}' +
        '.admin-shell{display:block !important;padding:0 !important;margin:0 !important}' +
        '.admin-shell>.admin-content,.admin-content{padding:0 !important;margin:0 !important;max-width:none !important}' +
        'body{margin:0 !important;background:#fff !important}' +
        '.dashboard-hero-card{display:none !important}';

    function stripChrome() {
        try {
            const doc = frame.contentDocument;
            if (!doc || !doc.head) return;
            let style = doc.getElementById('mb-chrome-hide');
            if (!style) {
                style = doc.createElement('style');
                style.id = 'mb-chrome-hide';
                style.textContent = HIDE_DUPLICATE_CHROME;
                doc.head.appendChild(style);
            }
        } catch (e) { /* cross-origin — same-origin here, so ignore */ }
    }

    function showTab(key, url) {
        btns.forEach(b => {
            const on = b.getAttribute('data-mb-key') === key;
            b.style.background = on ? 'var(--admin-primary)' : 'transparent';
            b.style.color = on ? '#fff' : '#64748b';
            b.style.boxShadow = on ? '0 1px 6px rgba(27,94,32,0.25)' : 'none';
        });
        try { sessionStorage.setItem(storeKey, key); } catch (e) {}
        if (frame.getAttribute('src') === url) return;
        spinner.classList.remove('hidden');
        frame.src = url;
    }

    btns.forEach(b => {
        b.addEventListener('click', () => showTab(b.getAttribute('data-mb-key'), b.getAttribute('data-mb-url')));
    });

    frame.addEventListener('load', () => {
        stripChrome();
        spinner.classList.add('hidden');
    });

    // Start on the last-used tool inside this group (default: first tab).
    let initialKey = btns.length ? btns[0].getAttribute('data-mb-key') : '';
    try {
        const saved = sessionStorage.getItem(storeKey);
        if (saved && btns.some(b => b.getAttribute('data-mb-key') === saved)) initialKey = saved;
    } catch (e) {}
    const first = btns.find(b => b.getAttribute('data-mb-key') === initialKey) || btns[0];
    if (first) showTab(first.getAttribute('data-mb-key'), first.getAttribute('data-mb-url'));
})();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
