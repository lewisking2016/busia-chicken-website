<?php
/**
 * Chicken Recipes & Blog Page
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Chicken Recipes & Culinary Ideas | Busia Chicken Farm';

include '../includes/header.php';

$pdo = getDB();
$recipes = [];

if ($pdo) {
    try {
        $recipes = $pdo->query("SELECT * FROM recipes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        @error_log("Failed to fetch recipes from database: " . $e->getMessage());
    }
}

// No fallback data — show empty state if DB has no recipes yet
$db_error = empty($recipes) && !$pdo;
?>

<!-- Recipes Hero -->
<section style="padding: var(--space-4xl) 0 var(--space-2xl); background-image: url('/Frontend/images/adbg.png'); background-size: cover; background-position: center; background-repeat: no-repeat; border-bottom: 1px solid rgba(255,255,255,0.12); position: relative;">
    <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.35);"></div>
    <div class="container" style="position: relative; z-index: 1; text-align: center; color: #FFFFFF;">
        <h1 style="margin-bottom: var(--space-sm); color: #FFFFFF;">Delicious Recipes</h1>
        <p style="font-size: 1.125rem; color: rgba(255,255,255,0.9);">Culinary ideas and cooking tips for our farm-fresh eggs and poultry meats.</p>
    </div>
</section>

<!-- Recipes Grid -->
<section style="padding: var(--space-4xl) 0; background: #ffffff;">
    <div class="container">
        
        <?php if ($db_error): ?>
        <div style="text-align: center; padding: 80px 20px;">
            <i data-lucide="wifi-off" style="width: 56px; height: 56px; color: var(--gray-400); margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;"></i>
            <h3 style="color: var(--gray-700); margin-bottom: 12px;">Service Unavailable</h3>
            <p style="color: var(--gray-500);">We couldn't load recipes right now. Please try again shortly.</p>
        </div>
        <?php elseif (empty($recipes)): ?>
        <div style="text-align: center; padding: 80px 20px;">
            <i data-lucide="book-open" style="width: 56px; height: 56px; color: var(--gray-400); margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;"></i>
            <h3 style="color: var(--gray-700); margin-bottom: 12px;">No Recipes Yet</h3>
            <p style="color: var(--gray-500);">Our culinary collection is being prepared. Check back soon!</p>
        </div>
        <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php foreach ($recipes as $r): ?>
                <div class="admin-card" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg); border: 1px solid rgba(0,0,0,0.06); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <div style="height: 200px; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($r['image_url'] ?? '/Frontend/images/download (4).png'); ?>" 
                             alt="<?php echo htmlspecialchars($r['title']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.src='/Frontend/images/download (4).png'">
                    </div>
                    <div style="padding: 24px; flex: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 0.75rem; color: var(--gray-500); margin-bottom: 8px; display: block;">
                            <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
                        </span>
                        <h4 style="font-weight: 700; margin-bottom: 12px; line-height: 1.3;">
                            <?php echo htmlspecialchars($r['title']); ?>
                        </h4>
                        <p style="color: var(--gray-600); font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px;">
                            <?php echo htmlspecialchars(mb_substr($r['content'], 0, 160)); ?>...
                        </p>
                        <a href="/Frontend/pages/recipe-detail.php?id=<?php echo (int)$r['id']; ?>" style="color: var(--primary); font-weight: 700; text-decoration: none; font-size: 0.88rem; margin-top: auto; display: inline-flex; align-items: center; gap: 4px;">
                            Read Full Recipe <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php
include '../includes/footer.php';
?>
