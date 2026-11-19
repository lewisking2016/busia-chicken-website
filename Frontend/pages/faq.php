<?php
/**
 * FAQ Page — Busia Chicken Limited
 *
 * Questions and answers are managed in Admin → Website CMS → FAQ Items,
 * grouped by category. The page copy is managed under FAQ Page.
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'FAQs - Busia Chicken Limited | The Chicken People';

include __DIR__ . '/../includes/header.php';

// ── Load FAQ items (grouped by category, ordered) ────────────────
$faqGroups = [];
$pdo = function_exists('getDB') ? getDB() : null;
if ($pdo) {
    try {
        $rows = $pdo->query('SELECT category, question, answer FROM cms_faq_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $faqGroups[$row['category']][] = $row;
        }
    } catch (Throwable $e) {
        $faqGroups = [];
    }
}
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('<?php echo htmlspecialchars(cms_img('faq', 'hero_image', '/Frontend/images/adbg.webp'), ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>FAQ</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100"><?php echo cms_html('faq', 'hero_title'); ?></h1>
        <p data-reveal="fade" data-reveal-delay="200"><?php echo cms_text('faq', 'hero_subtitle'); ?></p>
    </div>
</section>

<!-- FAQ Sections -->
<section style="padding: var(--space-3xl) 0; background-color: var(--white);">
    <div class="container" style="max-width: 900px;">
        <?php if (!$faqGroups): ?>
            <p style="text-align:center;color:var(--gray-500);">Our FAQ is being updated. Please <a href="/Frontend/pages/contact.php">contact us</a> with any question.</p>
        <?php endif; ?>

        <?php $groupIndex = 0; foreach ($faqGroups as $category => $items): $groupIndex++; ?>
        <div style="margin-bottom: var(--space-3xl);">
            <h2 style="margin-bottom: var(--space-xl);"><?php echo htmlspecialchars((string)$category, ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php foreach ($items as $i => $item): ?>
            <div class="faq-item fade-up <?php echo $i > 0 ? 'stagger-' . min($i, 4) : ''; ?>">
                <div class="faq-question">
                    <span><?php echo htmlspecialchars($item['question'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="faq-toggle">▼</div>
                </div>
                <div class="faq-answer">
                    <?php echo htmlspecialchars($item['answer'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Still Need Help -->
<section style="padding: var(--space-3xl) 0; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: var(--white); text-align: center;">
    <div class="container">
        <h2 style="color: var(--white); margin-bottom: var(--space-md);"><?php echo cms_text('faq', 'help_title'); ?></h2>
        <p style="opacity: 0.9; margin-bottom: var(--space-xl);"><?php echo cms_text('faq', 'help_text'); ?></p>
        <a href="<?php echo htmlspecialchars(cms('faq', 'help_link', '/Frontend/pages/contact.php'), ENT_QUOTES); ?>" class="btn btn-accent"><?php echo cms_text('faq', 'help_button'); ?></a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');

            // Close all other items
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
            });

            // Toggle current item
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
});
</script>

<?php
include __DIR__ . '/../includes/footer.php';
?>
